<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CancellationRequest;
use App\Models\ItemIncident;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = today();

        // Sewa yang sedang berlangsung hari ini (sudah dikonfirmasi / alat sudah diambil).
        $activeToday = Booking::query()
            ->withCount('items')
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PICKED_UP])
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('start_date')
            ->get();

        $pickupsToday = $activeToday->filter(
            fn ($b) => $b->status === Booking::STATUS_CONFIRMED && $b->start_date->isSameDay($today)
        )->count();

        $returnsToday = $activeToday->filter(fn ($b) => $b->end_date->isSameDay($today))->count();

        // Alat sudah diambil tetapi melewati tanggal selesai -> terlambat dikembalikan.
        $overdue = Booking::where('status', Booking::STATUS_PICKED_UP)
            ->whereDate('end_date', '<', $today)
            ->count();

        // Pendapatan simulasi: total sewa booking lunas (deposit tidak dihitung karena dikembalikan).
        $paid = Booking::where('payment_status', Booking::PAYMENT_PAID);
        $income = [
            'month' => (int) (clone $paid)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount'),
            'total' => (int) (clone $paid)->sum('total_amount'),
        ];

        $stats = [
            'cancelPending' => CancellationRequest::where('status', CancellationRequest::STATUS_PENDING)->count(),
            'incidentCount' => ItemIncident::whereIn('status', [ItemIncident::STATUS_REPORTED, ItemIncident::STATUS_BILLED])->count(),
            'incidentSum'   => (int) ItemIncident::whereIn('status', [ItemIncident::STATUS_REPORTED, ItemIncident::STATUS_BILLED])->sum('replacement_cost'),
        ];

        $recent = Booking::latest()->take(10)->get();

        return view('staff.dashboard', compact(
            'activeToday', 'pickupsToday', 'returnsToday', 'overdue', 'income', 'stats', 'recent'
        ));
    }
}
