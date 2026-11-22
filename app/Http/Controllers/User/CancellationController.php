<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CancellationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancellationController extends Controller
{
    /** Ajukan pembatalan. Keputusan (setuju/tolak) ada di tangan staff. */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
            'reason.min'      => 'Alasan pembatalan minimal 10 karakter.',
            'reason.max'      => 'Alasan pembatalan maksimal 500 karakter.',
        ]);

        // Cek ulang di dalam transaksi agar klik ganda tidak membuat dua pengajuan.
        $created = DB::transaction(function () use ($booking, $request, $data) {
            if (! $booking->isCancellable()) {
                return false;
            }

            CancellationRequest::create([
                'booking_id' => $booking->id,
                'user_id'    => $request->user()->id,
                'reason'     => $data['reason'],
                'status'     => CancellationRequest::STATUS_PENDING,
            ]);

            return true;
        });

        if (! $created) {
            return redirect()->route('user.bookings.show', $booking)
                ->with('error', 'Booking ini tidak dapat dibatalkan (sudah berjalan, selesai, atau sudah ada pengajuan yang menunggu).');
        }

        return redirect()->route('user.bookings.show', $booking)
            ->with('success', 'Pengajuan pembatalan terkirim. Mohon tunggu keputusan dari staf.');
    }
}
