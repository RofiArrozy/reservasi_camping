<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\CancellationRequest;
use App\Models\Item;
use App\Models\ItemIncident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Data booking dummy untuk mengisi dashboard dan grafik analitik.
 * Semua tanggal relatif terhadap hari ini, sehingga selalu terlihat "hidup".
 * Catatan: seeder ini tidak idempotent; jalankan lewat migrate:fresh --seed.
 */
class BookingSeeder extends Seeder
{
    private $items;
    private int $counter = 0;

    public function run(): void
    {
        $this->items = Item::all()->keyBy('slug');
        $today = Carbon::today();

        $staff = User::where('email', 'staff@demo.test')->firstOrFail();
        $demo  = User::where('email', 'user@demo.test')->firstOrFail();
        $budi  = User::where('email', 'budi@demo.test')->firstOrFail();
        $siti  = User::where('email', 'siti@demo.test')->firstOrFail();
        $guest = ['name' => 'Rina Wulandari', 'email' => 'rina@example.test', 'phone' => '081355500123'];

        $done = Booking::STATUS_COMPLETED;
        $paid = Booking::PAYMENT_PAID;

        // ---------------------------------------------------------
        // 1) Riwayat selesai (untuk grafik periode sewa & pemakaian alat)
        // ---------------------------------------------------------
        $history = [
            [$demo, -80, 2, [['tenda-dome-4p', 1], ['sleeping-bag', 4], ['matras-gulung', 4], ['kompor-portable', 1], ['nesting-set', 1]]],
            [$budi, -68, 3, [['tenda-dome-2p', 1], ['sleeping-bag', 2], ['carrier-60l', 2], ['headlamp', 2]]],
            [$siti, -55, 2, [['hammock', 2], ['trekking-pole', 2], ['headlamp', 2]]],
            [$demo, -47, 3, [['tenda-dome-2p', 1], ['sleeping-bag', 2], ['matras-gulung', 2], ['lampu-tenda-led', 1]]],
            [null,  -40, 2, [['tenda-dome-4p', 1], ['kursi-lipat', 4], ['meja-lipat', 1], ['kompor-portable', 1]]],
            [$demo, -33, 2, [['tenda-dome-2p', 1], ['sleeping-bag', 2], ['kompor-portable', 1]]],
            [$budi, -26, 4, [['tenda-dome-2p', 2], ['sleeping-bag', 4], ['headlamp', 2], ['nesting-set', 1]]],
            [$siti, -19, 3, [['tenda-dome-4p', 1], ['sleeping-bag', 4], ['matras-gulung', 4], ['lampu-tenda-led', 2]]],
            [$demo, -12, 2, [['carrier-60l', 1], ['tenda-dome-2p', 1], ['trekking-pole', 2]]],
            [$budi, -6,  2, [['hammock', 2], ['kursi-lipat', 2], ['nesting-set', 1]]],
        ];

        $completed = [];
        foreach ($history as [$user, $offset, $days, $lines]) {
            $completed[] = $this->makeBooking($user, $today->copy()->addDays($offset), $days, $lines, $done, $paid, $guest);
        }

        // Insiden alat (fitur wajib Camping): satu rusak (ditagih), satu hilang (sudah dibayar)
        ItemIncident::create([
            'booking_id'       => $completed[1]->id,
            'item_id'          => $this->items['tenda-dome-2p']->id,
            'reported_by'      => $staff->id,
            'type'             => ItemIncident::TYPE_DAMAGED,
            'quantity'         => 1,
            'description'      => 'Flysheet sobek sekitar 20 cm di sisi kiri akibat tersangkut ranting.',
            'replacement_cost' => 150000,
            'status'           => ItemIncident::STATUS_BILLED,
        ]);

        ItemIncident::create([
            'booking_id'       => $completed[6]->id,
            'item_id'          => $this->items['headlamp']->id,
            'reported_by'      => $staff->id,
            'type'             => ItemIncident::TYPE_LOST,
            'quantity'         => 1,
            'description'      => 'Satu unit headlamp tidak dikembalikan.',
            'replacement_cost' => $this->items['headlamp']->replacement_cost,
            'status'           => ItemIncident::STATUS_PAID,
            'resolved_at'      => now(),
        ]);

        // ---------------------------------------------------------
        // 2) Sewa berjalan & akan datang (untuk dashboard "sewa hari ini")
        // ---------------------------------------------------------
        $this->makeBooking($demo, $today->copy()->subDay(), 3,
            [['tenda-dome-4p', 1], ['sleeping-bag', 4], ['matras-gulung', 4], ['lampu-tenda-led', 2]],
            Booking::STATUS_PICKED_UP, $paid, $guest);

        $this->makeBooking($siti, $today->copy(), 2,
            [['tenda-dome-2p', 1], ['sleeping-bag', 2], ['hammock', 1]],
            Booking::STATUS_CONFIRMED, $paid, $guest);

        $this->makeBooking($demo, $today->copy()->addDays(7), 3,
            [['tenda-dome-2p', 1], ['sleeping-bag', 2], ['kompor-portable', 1], ['nesting-set', 1]],
            Booking::STATUS_CONFIRMED, $paid, $guest);

        // Booking pending (belum dibayar), termasuk satu dari tamu untuk demo Booking Tracker
        $this->makeBooking($siti, $today->copy()->addDays(20), 2,
            [['carrier-60l', 2], ['trekking-pole', 2]],
            Booking::STATUS_PENDING, Booking::PAYMENT_UNPAID, $guest);

        $this->makeBooking(null, $today->copy()->addDays(15), 2,
            [['tenda-dome-4p', 1], ['kursi-lipat', 4]],
            Booking::STATUS_PENDING, Booking::PAYMENT_UNPAID, $guest);

        // ---------------------------------------------------------
        // 3) Pengajuan pembatalan: pending, disetujui, ditolak
        // ---------------------------------------------------------
        // Pending (menunggu keputusan staff)
        $b = $this->makeBooking($demo, $today->copy()->addDays(14), 2,
            [['tenda-dome-2p', 1], ['sleeping-bag', 2]], Booking::STATUS_CONFIRMED, $paid, $guest);
        $this->requestCancellation($b, $demo, 'Jadwal kegiatan berubah, mohon dibatalkan.');

        $b = $this->makeBooking($budi, $today->copy()->addDays(12), 2,
            [['hammock', 2], ['headlamp', 2]], Booking::STATUS_CONFIRMED, $paid, $guest);
        $this->requestCancellation($b, $budi, 'Anggota rombongan berhalangan hadir.');

        // Disetujui -> booking cancelled, stok lepas, deposit dicatat dikembalikan
        $b = $this->makeBooking($demo, $today->copy()->addDays(9), 2,
            [['tenda-dome-4p', 1], ['sleeping-bag', 4]], Booking::STATUS_CONFIRMED, $paid, $guest);
        $this->requestCancellation($b, $demo, 'Sakit, tidak bisa berangkat.')
            ->approve($staff, 'Disetujui. Deposit dikembalikan penuh.');

        // Ditolak -> booking tetap aktif
        $b = $this->makeBooking($demo, $today->copy()->addDays(2), 2,
            [['kompor-portable', 1], ['nesting-set', 1]], Booking::STATUS_CONFIRMED, $paid, $guest);
        $this->requestCancellation($b, $demo, 'Berubah pikiran.')
            ->reject($staff, 'Ditolak karena jadwal sewa sudah terlalu dekat dan alat telah disiapkan.');

        // Pembatalan lampau (untuk grafik pembatalan)
        $b = $this->makeBooking($budi, $today->copy()->subDays(30), 2,
            [['tenda-dome-2p', 1], ['sleeping-bag', 2]], Booking::STATUS_CONFIRMED, $paid, $guest);
        $this->requestCancellation($b, $budi, 'Cuaca buruk di lokasi tujuan.', $b->created_at->copy()->addDay())
            ->approve($staff, 'Disetujui karena alasan cuaca.');

        // ---------------------------------------------------------
        // Ringkasan untuk demo Booking Tracker
        // ---------------------------------------------------------
        $this->command?->info('Kode booking Demo User (untuk uji Booking Tracker, kontak: user@demo.test):');
        Booking::where('user_id', $demo->id)->orderBy('start_date')->get()
            ->each(fn (Booking $x) => $this->command?->line("  {$x->booking_code}  [{$x->status}]  {$x->start_date->toDateString()}"));
    }

    /**
     * Buat satu booking beserta rincian alatnya.
     *
     * @param  array<int, array{0:string,1:int}>  $lines  [[slug alat, jumlah], ...]
     */
    private function makeBooking(?User $user, Carbon $start, int $days, array $lines, string $status, string $payment, array $guest): Booking
    {
        $name  = $user?->name  ?? $guest['name'];
        $email = $user?->email ?? $guest['email'];
        $phone = $user?->phone ?? $guest['phone'];

        $perDay   = collect($lines)->sum(fn ($l) => $this->items[$l[0]]->daily_rate * $l[1]);
        $subtotal = $perDay * $days;
        $deposit  = (int) (round($subtotal * 0.2 / 1000) * 1000);   // deposit dummy 20%

        $created = $start->copy()->subDays(3)->setTime(10, 0);
        if ($created->isFuture()) {
            $created = now()->subHours(6);
        }

        $booking = new Booking([
            'user_id'        => $user?->id,
            'customer_name'  => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'start_date'     => $start,
            'end_date'       => $start->copy()->addDays($days - 1),
            'total_days'     => $days,
            'subtotal'       => $subtotal,
            'deposit_amount' => $deposit,
            'total_amount'   => $subtotal,
            'status'         => $status,
            'payment_status' => $payment,
            'payment_method' => $payment === Booking::PAYMENT_UNPAID
                ? null
                : ($this->counter++ % 2 === 0 ? 'QRIS (Dummy)' : 'Transfer Bank (Dummy)'),
        ]);
        $booking->created_at = $created;
        $booking->updated_at = $created;
        $booking->save();

        foreach ($lines as [$slug, $qty]) {
            $item = $this->items[$slug];
            $booking->items()->create([
                'item_id'    => $item->id,
                'quantity'   => $qty,
                'daily_rate' => $item->daily_rate,
                'subtotal'   => $item->daily_rate * $qty * $days,
            ]);
        }

        return $booking;
    }

    private function requestCancellation(Booking $booking, User $user, string $reason, ?Carbon $createdAt = null): CancellationRequest
    {
        $request = new CancellationRequest([
            'booking_id' => $booking->id,
            'user_id'    => $user->id,
            'reason'     => $reason,
            'status'     => CancellationRequest::STATUS_PENDING,
        ]);
        $request->created_at = $createdAt ?? now()->subDay();
        $request->updated_at = $request->created_at;
        $request->save();

        return $request;
    }
}
