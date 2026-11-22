<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Package;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    /** Metode pembayaran simulasi (sandbox). Kunci = nilai form, nilai = label yang disimpan. */
    private const PAYMENT_METHODS = [
        'qris'          => 'QRIS (Dummy)',
        'bank_transfer' => 'Transfer Bank (Dummy)',
        'ewallet'       => 'E-Wallet (Dummy)',
    ];

    /** Form pemesanan. Dapat diisi awal lewat ?item=slug atau ?package=slug. */
    public function create(Request $request): View
    {
        $selectedItems    = [];
        $selectedPackages = [];

        if ($slug = $request->string('item')->toString()) {
            if ($id = Item::active()->where('slug', $slug)->value('id')) {
                $selectedItems[$id] = 1;
            }
        }

        if ($slug = $request->string('package')->toString()) {
            if ($id = Package::active()->where('slug', $slug)->value('id')) {
                $selectedPackages[$id] = 1;
            }
        }

        return view('booking.create', [
            'itemsByCategory'  => Item::active()->with('category')->orderBy('name')->get()->groupBy('category.name'),
            'packages'         => Package::active()->with('items')->orderBy('name')->get(),
            'selectedItems'    => $selectedItems,
            'selectedPackages' => $selectedPackages,
            'today'            => now()->toDateString(),
            'defaultStart'     => now()->addDay()->toDateString(),
            'defaultEnd'       => now()->addDays(2)->toDateString(),
        ]);
    }

    /** Endpoint JSON: stok tersedia per alat pada rentang tanggal (untuk tampilan langsung di form). */
    public function availability(Request $request, BookingService $service): JsonResponse
    {
        $data = $request->validate([
            'start' => ['required', 'date', 'after_or_equal:today'],
            'end'   => ['required', 'date', 'after_or_equal:start'],
        ]);

        return response()->json((object) $service->availability($data['start'], $data['end'])->all());
    }

    public function store(StoreBookingRequest $request, BookingService $service): RedirectResponse
    {
        $booking = $service->create($request->validated(), $request->user());

        return redirect()
            ->route('booking.show', ['booking' => $booking->qr_token])
            ->with('success', 'Booking berhasil dibuat! Simpan kode booking Anda untuk melacak status.');
    }

    /**
     * Halaman ringkasan booking. Diakses lewat token acak (qr_token) yang tidak dapat ditebak,
     * bukan ID berurutan.
     */
    public function show(Booking $booking): View
    {
        $booking->load('items.item', 'items.package');

        return view('booking.show', [
            'booking'        => $booking,
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    /**
     * Simulasi pembayaran (sandbox): tidak ada uang sungguhan.
     * Booking yang belum dibayar menjadi lunas dan otomatis berstatus "confirmed".
     */
    public function pay(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'method' => ['required', Rule::in(array_keys(self::PAYMENT_METHODS))],
        ], [
            'method.required' => 'Pilih metode pembayaran.',
            'method.in'       => 'Metode pembayaran tidak valid.',
        ]);

        // Update bersyarat (atomik): hanya berhasil jika booking masih pending & belum dibayar.
        // Mencegah pembayaran ganda walau tombol ditekan dua kali atau form dikirim ulang.
        $updated = Booking::whereKey($booking->id)
            ->where('status', Booking::STATUS_PENDING)
            ->where('payment_status', Booking::PAYMENT_UNPAID)
            ->update([
                'payment_status' => Booking::PAYMENT_PAID,
                'payment_method' => self::PAYMENT_METHODS[$data['method']],
                'status'         => Booking::STATUS_CONFIRMED,
            ]);

        $redirect = redirect()->route('booking.show', ['booking' => $booking->qr_token]);

        return $updated
            ? $redirect->with('success', 'Pembayaran (simulasi) berhasil. Booking Anda telah dikonfirmasi.')
            : $redirect->with('error', 'Booking ini tidak dapat dibayar (sudah dibayar atau dibatalkan).');
    }
}
