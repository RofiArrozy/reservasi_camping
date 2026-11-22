<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CancellationRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'total'    => $user->bookings()->count(),
            'active'   => $user->bookings()
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_PICKED_UP])->count(),
            'unpaid'   => $user->bookings()
                ->where('status', Booking::STATUS_PENDING)
                ->where('payment_status', Booking::PAYMENT_UNPAID)->count(),
            'cancelPending' => $user->cancellationRequests()
                ->where('status', CancellationRequest::STATUS_PENDING)->count(),
        ];

        // Booking terdekat yang belum berjalan.
        $upcoming = $user->bookings()
            ->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
            ->whereDate('start_date', '>=', today())
            ->orderBy('start_date')
            ->first();

        $recent = $user->bookings()->latest('start_date')->take(3)->get();

        return view('user.dashboard', compact('stats', 'upcoming', 'recent'));
    }
}
