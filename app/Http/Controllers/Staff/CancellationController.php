<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CancellationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

/**
 * Manajemen pengajuan pembatalan (Staff & Admin).
 */
class CancellationController extends Controller
{
    private const TABS = ['pending', 'approved', 'rejected', 'all'];

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), self::TABS, true) ? $request->query('status') : 'pending';

        $counts = CancellationRequest::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $cancellations = CancellationRequest::with(['booking.items.item', 'user', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            // Yang menunggu: terlama dulu (antrean). Lainnya: terbaru dulu.
            ->when(
                $status === 'pending',
                fn ($q) => $q->oldest(),
                fn ($q) => $q->latest()
            )
            ->paginate(8)
            ->withQueryString();

        return view('staff.cancellations.index', [
            'cancellations' => $cancellations,
            'status'        => $status,
            'counts'        => $counts,
        ]);
    }

    public function approve(Request $request, CancellationRequest $cancellation): RedirectResponse
    {
        $data = $request->validate([
            'staff_note' => ['nullable', 'string', 'max:300'],
        ], [
            'staff_note.max' => 'Catatan maksimal 300 karakter.',
        ]);

        try {
            $cancellation->approve($request->user(), $data['staff_note'] ?? null);
        } catch (LogicException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::record(
            'cancellation.approved',
            $cancellation,
            "Menyetujui pembatalan booking {$cancellation->booking->booking_code}",
            ['booking_code' => $cancellation->booking->booking_code, 'note' => $data['staff_note'] ?? null]
        );

        return back()->with('success', "Pembatalan {$cancellation->booking->booking_code} disetujui. Stok dilepas dan deposit dicatat.");
    }

    public function reject(Request $request, CancellationRequest $cancellation): RedirectResponse
    {
        $data = $request->validate([
            'staff_note' => ['required', 'string', 'min:5', 'max:300'],
        ], [
            'staff_note.required' => 'Alasan penolakan wajib diisi.',
            'staff_note.min'      => 'Alasan penolakan minimal 5 karakter.',
            'staff_note.max'      => 'Alasan penolakan maksimal 300 karakter.',
        ]);

        try {
            $cancellation->reject($request->user(), $data['staff_note']);
        } catch (LogicException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::record(
            'cancellation.rejected',
            $cancellation,
            "Menolak pembatalan booking {$cancellation->booking->booking_code}",
            ['booking_code' => $cancellation->booking->booking_code, 'note' => $data['staff_note']]
        );

        return back()->with('success', "Pembatalan {$cancellation->booking->booking_code} ditolak.");
    }
}
