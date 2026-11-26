<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Item;
use App\Models\ItemIncident;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * Alur operasional sewa oleh staf: alat diambil, alat dikembalikan,
 * serta pencatatan alat rusak/hilang beserta tagihan ganti rugi.
 */
class RentalService
{
    /** Booking dikonfirmasi -> alat sudah diambil. */
    public function markPickedUp(Booking $booking): void
    {
        if ($booking->start_date->gt(today())) {
            throw new LogicException(
                'Alat belum dapat diambil sebelum tanggal mulai sewa (' . $booking->start_date->translatedFormat('d F Y') . ').'
            );
        }

        // Update bersyarat (atomik): hanya jika status masih "confirmed".
        $affected = Booking::whereKey($booking->id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->update(['status' => Booking::STATUS_PICKED_UP]);

        if ($affected === 0) {
            throw new LogicException('Status booking sudah berubah atau belum dikonfirmasi. Muat ulang halaman.');
        }
    }

    /** Alat sudah diambil -> alat dikembalikan, sewa selesai (stok terlepas otomatis). */
    public function markReturned(Booking $booking): void
    {
        $affected = Booking::whereKey($booking->id)
            ->where('status', Booking::STATUS_PICKED_UP)
            ->update(['status' => Booking::STATUS_COMPLETED]);

        if ($affected === 0) {
            throw new LogicException('Booking ini belum berstatus "Sedang Disewa" atau sudah diselesaikan. Muat ulang halaman.');
        }
    }

    /**
     * Catat alat rusak/hilang.
     * - Jumlah tidak boleh melebihi jumlah yang disewa (dikurangi yang sudah dilaporkan).
     * - Biaya default = nilai penggantian alat x jumlah, dapat disesuaikan staf (mis. kerusakan sebagian).
     * - Alat HILANG mengurangi total stok alat; kerusakan tidak mengubah stok.
     *
     * @param  array{item_id:int, type:string, quantity:int, description:?string, replacement_cost:?int}  $data
     * @throws ValidationException
     */
    public function reportIncident(Booking $booking, array $data, User $reporter): ItemIncident
    {
        if (! in_array($booking->status, [Booking::STATUS_PICKED_UP, Booking::STATUS_COMPLETED], true)) {
            throw ValidationException::withMessages([
                'item_id' => 'Insiden hanya dapat dicatat untuk booking yang alatnya sudah diambil atau sudah selesai.',
            ]);
        }

        return DB::transaction(function () use ($booking, $data, $reporter) {
            $booking->load('items');

            $booked = (int) $booking->items->where('item_id', $data['item_id'])->sum('quantity');
            if ($booked === 0) {
                throw ValidationException::withMessages(['item_id' => 'Alat tersebut tidak termasuk dalam booking ini.']);
            }

            $alreadyReported = (int) $booking->incidents()->where('item_id', $data['item_id'])->sum('quantity');
            $remaining       = $booked - $alreadyReported;

            if ($data['quantity'] > $remaining) {
                throw ValidationException::withMessages([
                    'quantity' => $remaining > 0
                        ? "Jumlah melebihi sisa yang dapat dilaporkan ({$remaining} unit)."
                        : 'Seluruh unit alat ini sudah dilaporkan pada booking ini.',
                ]);
            }

            $item = Item::withTrashed()->findOrFail($data['item_id']);
            $cost = $data['replacement_cost'] ?? ($item->replacement_cost * $data['quantity']);

            $incident = ItemIncident::create([
                'booking_id'       => $booking->id,
                'item_id'          => $item->id,
                'reported_by'      => $reporter->id,
                'type'             => $data['type'],
                'quantity'         => $data['quantity'],
                'description'      => $data['description'] ?? null,
                'replacement_cost' => $cost,
                'status'           => ItemIncident::STATUS_BILLED,
            ]);

            if ($data['type'] === ItemIncident::TYPE_LOST) {
                $item->update(['total_stock' => max(0, $item->total_stock - $data['quantity'])]);
            }

            return $incident;
        });
    }

    /**
     * Ubah status tagihan ganti rugi: billed (ditagih), paid (lunas), atau waived (dibebaskan).
     * Hanya insiden yang masih terbuka (reported/billed) yang dapat diubah.
     *
     * @throws LogicException
     */
    public function updateIncidentStatus(ItemIncident $incident, string $status): void
    {
        $affected = ItemIncident::whereKey($incident->id)
            ->whereIn('status', [ItemIncident::STATUS_REPORTED, ItemIncident::STATUS_BILLED])
            ->update([
                'status'      => $status,
                'resolved_at' => in_array($status, [ItemIncident::STATUS_PAID, ItemIncident::STATUS_WAIVED], true) ? now() : null,
            ]);

        if ($affected === 0) {
            throw new LogicException('Tagihan ini sudah diselesaikan sebelumnya.');
        }
    }
}
