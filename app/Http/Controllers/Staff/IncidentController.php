<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\ItemIncident;
use App\Services\RentalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use LogicException;

/**
 * Pencatatan & penagihan alat rusak/hilang.
 */
class IncidentController extends Controller
{
    private const TABS = ['open', 'paid', 'waived', 'all'];

    public function index(Request $request): View
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'open';

        $open = [ItemIncident::STATUS_REPORTED, ItemIncident::STATUS_BILLED];

        $counts = [
            'open'   => ItemIncident::whereIn('status', $open)->count(),
            'paid'   => ItemIncident::where('status', ItemIncident::STATUS_PAID)->count(),
            'waived' => ItemIncident::where('status', ItemIncident::STATUS_WAIVED)->count(),
        ];
        $counts['all'] = array_sum($counts);

        $incidents = ItemIncident::with(['booking', 'item', 'reporter'])
            ->when($tab === 'open', fn ($q) => $q->whereIn('status', $open))
            ->when($tab === 'paid', fn ($q) => $q->where('status', ItemIncident::STATUS_PAID))
            ->when($tab === 'waived', fn ($q) => $q->where('status', ItemIncident::STATUS_WAIVED))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $openSum = (int) ItemIncident::whereIn('status', $open)->sum('replacement_cost');

        return view('staff.incidents.index', compact('incidents', 'tab', 'counts', 'openSum'));
    }

    public function store(Request $request, Booking $booking, RentalService $rental): RedirectResponse
    {
        $data = $request->validate([
            'item_id'          => ['required', 'integer'],
            'type'             => ['required', Rule::in([ItemIncident::TYPE_DAMAGED, ItemIncident::TYPE_LOST])],
            'quantity'         => ['required', 'integer', 'min:1', 'max:99'],
            'replacement_cost' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'description'      => ['nullable', 'string', 'max:300'],
        ], [
            'required' => ':attribute wajib diisi.',
            'integer'  => ':attribute harus berupa angka bulat.',
            'min'      => ':attribute minimal :min.',
            'max'      => ':attribute terlalu besar/panjang (maksimal :max).',
            'type.in'  => 'Jenis insiden tidak valid.',
        ], [
            'item_id'          => 'Alat',
            'type'             => 'Jenis insiden',
            'quantity'         => 'Jumlah',
            'replacement_cost' => 'Biaya ganti rugi',
            'description'      => 'Keterangan',
        ]);

        $incident = $rental->reportIncident($booking, $data, $request->user());

        ActivityLog::record(
            'incident.reported',
            $incident,
            "Mencatat alat " . ($incident->type === 'lost' ? 'hilang' : 'rusak') . " pada booking {$booking->booking_code}",
            ['item_id' => $incident->item_id, 'quantity' => $incident->quantity, 'cost' => $incident->replacement_cost]
        );

        return redirect()->route('staff.bookings.show', $booking)
            ->with('success', 'Insiden dicatat dan tagihan ganti rugi dibuat.');
    }

    public function update(Request $request, ItemIncident $incident, RentalService $rental): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([ItemIncident::STATUS_PAID, ItemIncident::STATUS_WAIVED])],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in'       => 'Status tidak valid.',
        ]);

        try {
            $rental->updateIncidentStatus($incident, $data['status']);
        } catch (LogicException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::record(
            'incident.' . $data['status'],
            $incident,
            'Mengubah status tagihan ganti rugi menjadi ' . ($data['status'] === 'paid' ? 'lunas' : 'dibebaskan'),
            ['booking_id' => $incident->booking_id, 'cost' => $incident->replacement_cost]
        );

        return back()->with('success', 'Status tagihan diperbarui.');
    }
}
