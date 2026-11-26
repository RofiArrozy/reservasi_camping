<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Item;

/**
 * Aturan inventaris: menjaga agar perubahan stok/penghapusan alat
 * tidak merusak booking yang sudah berjalan.
 */
class InventoryService
{
    /**
     * Jumlah unit alat yang dipesan pada puncak tertinggi di antara booking aktif/mendatang
     * (pending, confirmed, picked_up yang belum lewat tanggal selesainya).
     * Total stok tidak boleh diturunkan di bawah angka ini.
     */
    public function peakReservation(Item $item): int
    {
        $rows = BookingItem::query()
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->where('booking_items.item_id', $item->id)
            ->whereIn('bookings.status', Booking::STOCK_HOLDING_STATUSES)
            ->whereDate('bookings.end_date', '>=', today())
            ->get(['booking_items.quantity as qty', 'bookings.start_date as s', 'bookings.end_date as e'])
            ->map(fn ($r) => [
                'qty' => (int) $r->qty,
                's'   => substr((string) $r->s, 0, 10),
                'e'   => substr((string) $r->e, 0, 10),
            ]);

        if ($rows->isEmpty()) {
            return 0;
        }

        $today = today()->toDateString();

        // Beban tertinggi selalu terjadi pada salah satu tanggal mulai booking.
        return (int) $rows->pluck('s')
            ->map(fn ($d) => max($d, $today))
            ->unique()
            ->map(fn ($d) => $rows->filter(fn ($r) => $r['s'] <= $d && $r['e'] >= $d)->sum('qty'))
            ->max();
    }

    /** Jumlah booking aktif/mendatang yang memuat alat ini. */
    public function activeBookingCount(Item $item): int
    {
        return BookingItem::query()
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->where('booking_items.item_id', $item->id)
            ->whereIn('bookings.status', Booking::STOCK_HOLDING_STATUSES)
            ->whereDate('bookings.end_date', '>=', today())
            ->distinct()
            ->count('bookings.id');
    }
}
