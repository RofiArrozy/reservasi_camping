<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Item;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Logika bisnis pemesanan: cek ketersediaan, hitung harga, dan simpan booking.
 */
class BookingService
{
    /** Deposit dummy = 20% dari subtotal (dibulatkan ke ribuan). */
    public const DEPOSIT_RATE = 0.2;

    /** Batas maksimal lama sewa (hari). */
    public const MAX_DAYS = 14;

    /**
     * Stok tersedia untuk SEMUA alat aktif pada rentang tanggal tertentu.
     * Satu query agregat, lebih efisien daripada memanggil Item::availableStock() per alat.
     *
     * @return Collection<int, int>  [item_id => stok tersedia]
     */
    public function availability(
        CarbonInterface|string $start,
        CarbonInterface|string $end
    ): Collection {
        $startDate = Carbon::parse($start)->toDateString();
        $endDate   = Carbon::parse($end)->toDateString();

        $reserved = BookingItem::query()
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereIn('bookings.status', Booking::STOCK_HOLDING_STATUSES)
            ->whereDate('bookings.start_date', '<=', $endDate)
            ->whereDate('bookings.end_date', '>=', $startDate)
            ->groupBy('booking_items.item_id')
            ->selectRaw('booking_items.item_id as item_id, SUM(booking_items.quantity) as reserved')
            ->pluck('reserved', 'item_id');

        return Item::active()
            ->pluck('total_stock', 'id')
            ->map(fn ($total, $id) => max(0, (int) $total - (int) ($reserved[$id] ?? 0)));
    }

    /**
     * Ubah pilihan pengguna (alat satuan + paket) menjadi baris-baris rincian.
     * Paket diekspansi menjadi alat penyusunnya, dengan tarif sudah dipotong diskon paket.
     *
     * @param  array<int|string, int|string>  $itemQty     [item_id => jumlah]
     * @param  array<int|string, int|string>  $packageQty  [package_id => jumlah paket]
     * @return array<int, array{item: Item, package_id: ?int, quantity: int, unit_rate: int}>
     */
    public function buildLines(array $itemQty, array $packageQty): array
    {
        $itemQty    = array_filter(array_map('intval', $itemQty), fn ($q) => $q > 0);
        $packageQty = array_filter(array_map('intval', $packageQty), fn ($q) => $q > 0);

        $items    = Item::active()->whereIn('id', array_keys($itemQty))->get()->keyBy('id');
        $packages = Package::active()->with('items')->whereIn('id', array_keys($packageQty))->get()->keyBy('id');

        $lines = [];

        foreach ($itemQty as $id => $qty) {
            if (! $item = $items->get($id)) {
                continue;
            }
            $lines[] = [
                'item'       => $item,
                'package_id' => null,
                'quantity'   => $qty,
                'unit_rate'  => $item->daily_rate,
            ];
        }

        foreach ($packageQty as $id => $packageCount) {
            if (! $package = $packages->get($id)) {
                continue;
            }
            foreach ($package->items as $item) {
                $lines[] = [
                    'item'       => $item,
                    'package_id' => $package->id,
                    'quantity'   => $item->pivot->quantity * $packageCount,
                    'unit_rate'  => (int) round($item->daily_rate * (100 - $package->discount_percent) / 100),
                ];
            }
        }

        return $lines;
    }

    /**
     * Periksa kecukupan stok. Jumlah per alat digabung lintas baris
     * (alat yang dipilih satuan sekaligus lewat paket dihitung bersama).
     *
     * @return array<int, string>  Daftar pesan error (kosong jika stok aman)
     */
    public function stockErrors(array $lines, CarbonInterface|string $start, CarbonInterface|string $end): array
    {
        $available = $this->availability($start, $end);
        $errors    = [];

        $requested = collect($lines)->groupBy(fn ($line) => $line['item']->id);

        foreach ($requested as $itemId => $group) {
            $qty   = $group->sum('quantity');
            $stock = (int) ($available[$itemId] ?? 0);

            if ($qty > $stock) {
                $errors[] = "Stok {$group->first()['item']->name} tidak mencukupi pada tanggal tersebut "
                    . "(diminta {$qty}, tersedia {$stock}).";
            }
        }

        return $errors;
    }

    /**
     * Buat booking baru. Seluruh proses (cek stok ulang + simpan) berjalan dalam
     * satu transaksi database; harga selalu dihitung ulang di server.
     *
     * @param  array  $data  Data tervalidasi dari StoreBookingRequest
     * @throws ValidationException  Jika pilihan kosong, melebihi batas hari, atau stok tidak cukup
     */
    public function create(array $data, ?User $user = null): Booking
    {
        return DB::transaction(function () use ($data, $user) {
            $lines = $this->buildLines($data['items'] ?? [], $data['packages'] ?? []);

            if (empty($lines)) {
                throw ValidationException::withMessages(['items' => 'Pilih minimal satu alat atau paket.']);
            }

            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end   = Carbon::parse($data['end_date'])->startOfDay();
            $days  = (int) $start->diffInDays($end) + 1;

            if ($days > self::MAX_DAYS) {
                throw ValidationException::withMessages([
                    'end_date' => 'Maksimal lama sewa ' . self::MAX_DAYS . ' hari.',
                ]);
            }

            // Cek stok ulang di dalam transaksi (kondisi bisa berubah sejak form dibuka).
            if ($errors = $this->stockErrors($lines, $start, $end)) {
                throw ValidationException::withMessages(['items' => $errors]);
            }

            $subtotal = (int) collect($lines)->sum(fn ($l) => $l['unit_rate'] * $l['quantity'] * $days);
            $deposit  = (int) (round($subtotal * self::DEPOSIT_RATE / 1000) * 1000);

            $booking = Booking::create([
                'user_id'        => $user?->id,
                'customer_name'  => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'start_date'     => $start,
                'end_date'       => $end,
                'total_days'     => $days,
                'subtotal'       => $subtotal,
                'deposit_amount' => $deposit,
                'total_amount'   => $subtotal,
                'status'         => Booking::STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_UNPAID,
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $booking->items()->create([
                    'item_id'    => $line['item']->id,
                    'package_id' => $line['package_id'],
                    'quantity'   => $line['quantity'],
                    'daily_rate' => $line['unit_rate'],
                    'subtotal'   => $line['unit_rate'] * $line['quantity'] * $days,
                ]);
            }

            return $booking;
        });
    }
}
