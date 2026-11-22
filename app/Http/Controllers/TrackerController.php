<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Booking Tracker: pengunjung (tanpa login) melacak booking
 * dengan kode booking + email atau nomor telepon.
 */
class TrackerController extends Controller
{
    public function index(): View
    {
        return view('tracker.index');
    }

    public function search(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'code'    => ['required', 'string', 'max:30'],
            'contact' => ['required', 'string', 'max:255'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max'      => ':attribute terlalu panjang.',
        ], [
            'code'    => 'Kode booking',
            'contact' => 'Email atau nomor telepon',
        ]);

        $booking = Booking::track($data['code'], $data['contact'])
            ->with(['items.item', 'items.package', 'incidents.item'])
            ->first();

        // Pesan sengaja umum: tidak membocorkan apakah kode atau kontaknya yang salah.
        if (! $booking) {
            return back()->withInput()->withErrors([
                'code' => 'Booking tidak ditemukan. Periksa kembali kode booking dan email/nomor telepon Anda.',
            ]);
        }

        return view('tracker.result', compact('booking'));
    }
}
