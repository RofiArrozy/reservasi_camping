<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\CancellationRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Analitik pribadi user: periode sewa, pembatalan, dan pemakaian alat.
 *
 * Seluruh pengelompokan dilakukan dengan query standar dan Collection PHP,
 * tanpa fungsi tanggal khusus database, sehingga berjalan sama di SQLite maupun MySQL/PostgreSQL.
 */
class AnalyticsController extends Controller
{
    /** Booking yang dihitung sebagai "sewa sungguhan" (bukan pending/dibatalkan). */
    private const RENTAL_STATUSES = [
        Booking::STATUS_CONFIRMED,
        Booking::STATUS_PICKED_UP,
        Booking::STATUS_COMPLETED,
    ];

    public function __invoke(Request $request): View
    {
        $user    = $request->user();
        $rentals = $user->bookings()
            ->whereIn('status', self::RENTAL_STATUSES)
            ->get(['id', 'start_date', 'total_days', 'total_amount']);

        // ---------- Ringkasan ----------
        $summary = [
            'count'   => $rentals->count(),
            'days'    => (int) $rentals->sum('total_days'),
            'spent'   => (int) $rentals->sum('total_amount'),
            'avgDays' => $rentals->isEmpty() ? 0 : round($rentals->avg('total_days'), 1),
        ];

        // ---------- Grafik 1: sewa per bulan (6 bulan terakhir, bulan kosong = 0) ----------
        $perMonth = $rentals->groupBy(fn ($b) => $b->start_date->format('Y-m'));

        $monthly = collect(range(5, 0))->map(function (int $ago) use ($perMonth) {
            $month = now()->startOfMonth()->subMonths($ago);

            return [
                'label' => $month->translatedFormat('M Y'),
                'count' => $perMonth->get($month->format('Y-m'), collect())->count(),
            ];
        });

        // ---------- Grafik 2: sebaran lama sewa ----------
        $durations = ['1 hari' => 0, '2 hari' => 0, '3 hari' => 0, '4–7 hari' => 0, '8+ hari' => 0];
        foreach ($rentals as $booking) {
            $key = match (true) {
                $booking->total_days <= 1 => '1 hari',
                $booking->total_days === 2 => '2 hari',
                $booking->total_days === 3 => '3 hari',
                $booking->total_days <= 7 => '4–7 hari',
                default                    => '8+ hari',
            };
            $durations[$key]++;
        }

        // ---------- Grafik 3: pembatalan ----------
        $byStatus = $user->cancellationRequests()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $cancel = [
            'pending'  => (int) ($byStatus[CancellationRequest::STATUS_PENDING] ?? 0),
            'approved' => (int) ($byStatus[CancellationRequest::STATUS_APPROVED] ?? 0),
            'rejected' => (int) ($byStatus[CancellationRequest::STATUS_REJECTED] ?? 0),
        ];
        $totalBookings   = $user->bookings()->count();
        $cancelledCount  = $user->bookings()->where('status', Booking::STATUS_CANCELLED)->count();
        $cancel['rate']  = $totalBookings > 0 ? (int) round($cancelledCount / $totalBookings * 100) : 0;
        $cancel['cancelled'] = $cancelledCount;
        $cancel['total']     = $totalBookings;

        // ---------- Grafik 4: alat paling sering disewa ----------
        $usage = BookingItem::query()
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->where('bookings.user_id', $user->id)
            ->whereIn('bookings.status', self::RENTAL_STATUSES)
            ->groupBy('booking_items.item_id')
            ->selectRaw('booking_items.item_id as item_id, SUM(booking_items.quantity) as total_qty, COUNT(DISTINCT booking_items.booking_id) as times')
            ->orderByDesc('total_qty')
            ->limit(6)
            ->get();

        $names = Item::withTrashed()->whereIn('id', $usage->pluck('item_id'))->pluck('name', 'id');

        $topItems = $usage->map(fn ($row) => [
            'name'  => $names[$row->item_id] ?? 'Alat dihapus',
            'qty'   => (int) $row->total_qty,
            'times' => (int) $row->times,
        ]);

        return view('user.analytics', compact('summary', 'monthly', 'durations', 'cancel', 'topItems'));
    }
}
