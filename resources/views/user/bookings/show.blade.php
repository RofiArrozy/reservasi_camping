<x-layouts.dashboard :title="'Booking ' . $booking->booking_code" heading="Detail Booking">

    <a href="{{ route('user.bookings.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke riwayat</a>

    {{-- Header --}}
    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-bold tracking-wider">{{ $booking->booking_code }}</h2>
        <div class="flex flex-wrap gap-2">
            <x-booking-status :booking="$booking" />
            <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                {{ ['unpaid' => 'Belum dibayar', 'paid' => 'Lunas (simulasi)', 'refunded' => 'Dikembalikan'][$booking->payment_status] ?? $booking->payment_status }}
            </span>
        </div>
    </div>

    {{-- Tindakan cepat --}}
    @if ($booking->status === 'pending' && $booking->payment_status === 'unpaid')
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Booking ini menunggu pembayaran.
            <a href="{{ route('booking.show', ['booking' => $booking->qr_token]) }}" class="font-semibold underline">Selesaikan pembayaran</a>
        </div>
    @elseif (in_array($booking->status, ['confirmed', 'picked_up']) && Route::has('tracker.index'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            Tiket pengambilan tersedia di <a href="{{ route('tracker.index') }}" class="font-semibold underline">Lacak Booking</a>
            (gunakan kode booking dan email Anda).
        </div>
    @endif

    {{-- Informasi --}}
    <dl class="mt-4 grid gap-4 rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm sm:grid-cols-2">
        <div><dt class="text-gray-500">Periode sewa</dt>
            <dd class="font-medium">{{ $booking->start_date->translatedFormat('d F Y') }} &ndash; {{ $booking->end_date->translatedFormat('d F Y') }}</dd></div>
        <div><dt class="text-gray-500">Durasi</dt><dd class="font-medium">{{ $booking->total_days }} hari</dd></div>
        <div><dt class="text-gray-500">Metode pembayaran</dt><dd class="font-medium">{{ $booking->payment_method ?? '-' }}</dd></div>
        <div><dt class="text-gray-500">Dibuat pada</dt><dd class="font-medium">{{ $booking->created_at->translatedFormat('d F Y H:i') }}</dd></div>
        @if ($booking->notes)
            <div class="sm:col-span-2"><dt class="text-gray-500">Catatan</dt><dd class="font-medium">{{ $booking->notes }}</dd></div>
        @endif
    </dl>

    {{-- Rincian alat --}}
    <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                <tr><th class="px-4 py-3">Alat</th><th class="px-4 py-3 text-center">Jumlah</th><th class="px-4 py-3 text-right">Subtotal</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($booking->items as $line)
                    <tr>
                        <td class="px-4 py-3">{{ $line->item->name }}
                            @if ($line->package)<span class="block text-xs text-gray-500">dari {{ $line->package->name }}</span>@endif
                        </td>
                        <td class="px-4 py-3 text-center">{{ $line->quantity }}</td>
                        <td class="px-4 py-3 text-right">{{ \App\Support\Format::rupiah($line->subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr><td colspan="2" class="px-4 py-2 text-right text-gray-600">Total sewa</td>
                    <td class="px-4 py-2 text-right">{{ \App\Support\Format::rupiah($booking->total_amount) }}</td></tr>
                <tr><td colspan="2" class="px-4 py-2 text-right text-gray-600">Deposit</td>
                    <td class="px-4 py-2 text-right">{{ \App\Support\Format::rupiah($booking->deposit_amount) }}</td></tr>
                <tr class="font-bold"><td colspan="2" class="px-4 py-3 text-right">Total pembayaran</td>
                    <td class="px-4 py-3 text-right text-emerald-700">{{ \App\Support\Format::rupiah($booking->total_amount + $booking->deposit_amount) }}</td></tr>
            </tfoot>
        </table>
    </div>

    {{-- Alat rusak / hilang --}}
    @if ($booking->incidents->isNotEmpty())
        @php $labels = ['reported' => 'Dilaporkan', 'billed' => 'Ditagih', 'paid' => 'Lunas', 'waived' => 'Dibebaskan']; @endphp
        <section class="mt-4 rounded-xl border border-red-200 bg-red-50 p-5 text-sm">
            <h3 class="font-semibold text-red-900">Penggantian alat rusak / hilang</h3>
            <ul class="mt-3 space-y-2">
                @foreach ($booking->incidents as $incident)
                    <li class="rounded-lg bg-white p-3">
                        <div class="flex flex-wrap justify-between gap-2">
                            <span class="font-medium">{{ $incident->type === 'lost' ? 'Hilang' : 'Rusak' }}: {{ $incident->quantity }}× {{ $incident->item->name }}</span>
                            <span class="text-xs font-semibold text-gray-600">{{ $labels[$incident->status] ?? $incident->status }}</span>
                        </div>
                        @if ($incident->description)<p class="mt-1 text-xs text-gray-600">{{ $incident->description }}</p>@endif
                        <p class="mt-1 font-semibold">{{ \App\Support\Format::rupiah($incident->replacement_cost) }}</p>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Pembatalan --}}
    <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="font-semibold">Pembatalan</h3>

        @foreach ($booking->cancellationRequests->sortByDesc('created_at') as $request)
            <div class="mt-3 rounded-lg border border-gray-200 p-4 text-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <x-cancellation-status :status="$request->status" />
                    <span class="text-xs text-gray-500">Diajukan {{ $request->created_at->translatedFormat('d F Y H:i') }}</span>
                </div>
                <p class="mt-2"><span class="text-gray-500">Alasan:</span> {{ $request->reason }}</p>
                @if ($request->status !== 'pending')
                    @if ($request->staff_note)
                        <p class="mt-1"><span class="text-gray-500">Catatan staf:</span> {{ $request->staff_note }}</p>
                    @endif
                    @if ($request->deposit_refund_status === 'refunded')
                        <p class="mt-1 font-medium text-emerald-700">
                            Deposit tercatat dikembalikan: {{ \App\Support\Format::rupiah($request->deposit_refund_amount) }}
                        </p>
                    @endif
                @endif
            </div>
        @endforeach

        @if ($booking->isCancellable())
            <form method="POST" action="{{ route('user.cancellations.store', $booking) }}" class="mt-4"
                  onsubmit="return confirm('Kirim pengajuan pembatalan untuk booking ini?')">
                @csrf
                <label for="reason" class="block text-sm font-medium text-gray-700">Alasan pembatalan</label>
                <textarea id="reason" name="reason" rows="3" maxlength="500" required
                          class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 {{ $errors->has('reason') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500' }}">{{ old('reason') }}</textarea>
                @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-500">Minimal 10 karakter. Pengajuan akan ditinjau oleh staf; Anda bisa melihat hasilnya di halaman ini.</p>
                <x-button type="submit" variant="danger" class="mt-3">Ajukan Pembatalan</x-button>
            </form>
        @elseif ($booking->cancellationRequests->where('status', 'pending')->isNotEmpty())
            <p class="mt-4 text-sm text-gray-500">Pengajuan Anda sedang menunggu keputusan staf.</p>
        @elseif ($booking->cancellationRequests->isEmpty())
            <p class="mt-3 text-sm text-gray-500">Booking ini tidak dapat dibatalkan (sudah berjalan, selesai, atau dibatalkan).</p>
        @endif
    </section>

</x-layouts.dashboard>
