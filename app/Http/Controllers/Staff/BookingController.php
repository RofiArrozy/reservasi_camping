<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Services\RentalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

/**
 * Manajemen booking oleh Staff/Admin: daftar, detail, serta penandaan
 * alat diambil dan dikembalikan.
 */
class BookingController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'picked_up', 'completed', 'cancelled'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q'      => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:20'],
        ]);

        $q      = trim($filters['q'] ?? '');
        $status = in_array($filters['status'] ?? null, self::STATUSES, true) ? $filters['status'] : null;

        $bookings = Booking::query()
            ->withCount('items')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(fn ($w) => $w
                    ->where('booking_code', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('customer_email', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('staff.bookings.index', [
            'bookings' => $bookings,
            'q'        => $q,
            'status'   => $status,
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load(['items.item', 'items.package', 'incidents.item', 'incidents.reporter', 'cancellationRequests']);

        return view('staff.bookings.show', compact('booking'));
    }

    public function pickup(Booking $booking, RentalService $rental): RedirectResponse
    {
        try {
            $rental->markPickedUp($booking);
        } catch (LogicException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::record('booking.picked_up', $booking, "Menandai alat diambil: {$booking->booking_code}");

        return back()->with('success', "Booking {$booking->booking_code} ditandai alat sudah diambil.");
    }

    public function returned(Booking $booking, RentalService $rental): RedirectResponse
    {
        try {
            $rental->markReturned($booking);
        } catch (LogicException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::record('booking.returned', $booking, "Menandai alat dikembalikan: {$booking->booking_code}");

        return back()->with('success', "Booking {$booking->booking_code} selesai. Stok alat kembali tersedia.");
    }
}
