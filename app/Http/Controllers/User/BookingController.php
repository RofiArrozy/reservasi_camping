<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Riwayat sewa milik user yang sedang login.
 */
class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()->bookings()
            ->with('cancellationRequests')
            ->withCount('items')
            ->latest('start_date')
            ->paginate(8);

        return view('user.bookings.index', compact('bookings'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->ensureOwner($request, $booking);

        $booking->load(['items.item', 'items.package', 'cancellationRequests', 'incidents.item']);

        return view('user.bookings.show', compact('booking'));
    }

    /**
     * Booking milik orang lain dijawab 404 (bukan 403) agar ID booking
     * tidak bisa ditebak-tebak oleh pengguna lain.
     */
    private function ensureOwner(Request $request, Booking $booking): void
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
    }
}
