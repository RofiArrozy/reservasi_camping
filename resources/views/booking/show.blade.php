<x-layouts.app :title="'Booking ' . $booking->booking_code">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">

        {{-- Kode booking --}}
        <div class="rounded-2xl bg-emerald-700 p-6 text-center text-white shadow-sm">
            <p class="text-sm text-emerald-100">Kode booking Anda</p>
            <p class="mt-1 text-3xl font-bold tracking-widest">{{ $booking->booking_code }}</p>
            <p class="mt-3 text-sm text-emerald-50">
                Simpan kode ini. Anda memerlukannya bersama email atau nomor telepon untuk melacak status booking.
            </p>
        </div>

        {{-- Status --}}
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <span @class([
                'rounded-full px-3 py-1 text-sm font-semibold',
                'bg-amber-100 text-amber-800'     => $booking->status === 'pending',
                'bg-emerald-100 text-emerald-800' => in_array($booking->status, ['confirmed', 'picked_up', 'completed']),
                'bg-red-100 text-red-800'         => $booking->status === 'cancelled',
            ])>{{ $booking->status_label }}</span>

            <span @class([
                'rounded-full px-3 py-1 text-sm font-semibold',
                'bg-gray-100 text-gray-700'       => $booking->payment_status === 'unpaid',
                'bg-emerald-100 text-emerald-800' => $booking->payment_status === 'paid',
                'bg-sky-100 text-sky-800'         => $booking->payment_status === 'refunded',
            ])>Pembayaran: {{ ['unpaid' => 'Belum dibayar', 'paid' => 'Lunas (simulasi)', 'refunded' => 'Dikembalikan'][$booking->payment_status] ?? $booking->payment_status }}</span>
        </div>

        {{-- Panel pembayaran sesuai kondisi booking --}}
        @if ($booking->status === 'pending' && $booking->payment_status === 'unpaid')

            <form method="POST" action="{{ route('booking.pay', ['booking' => $booking->qr_token]) }}"
                  class="mt-6 rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50 p-5">
                @csrf
                <h2 class="font-semibold text-emerald-900">Pembayaran (Simulasi / Sandbox)</h2>
                <p class="mt-1 text-sm text-emerald-800">
                    Tidak ada transaksi uang sungguhan. Pilih metode lalu tekan tombol untuk mensimulasikan pembayaran.
                </p>

                <fieldset class="mt-4 space-y-2">
                    <legend class="sr-only">Metode pembayaran</legend>
                    @foreach ($paymentMethods as $key => $label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-emerald-200 bg-white px-4 py-3 text-sm">
                            <input type="radio" name="method" value="{{ $key }}"
                                   @checked(old('method', array_key_first($paymentMethods)) === $key)
                                   class="text-emerald-600 focus:ring-emerald-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </fieldset>
                @error('method') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                <x-button type="submit" class="mt-4 w-full sm:w-auto">
                    Bayar {{ \App\Support\Format::rupiah($booking->total_amount + $booking->deposit_amount) }} (Simulasi)
                </x-button>
            </form>

        @elseif ($booking->payment_status === 'paid')

            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
                <p class="font-semibold">Pembayaran diterima (simulasi) via {{ $booking->payment_method }}.</p>
                <p class="mt-1">Booking Anda sudah dikonfirmasi. Saat mengambil alat, tunjukkan kode booking di atas kepada petugas.</p>
                @if (Route::has('tracker.index'))
                    <p class="mt-2">
                        Tiket pengambilan dapat dilihat kapan saja lewat
                        <a href="{{ route('tracker.index') }}" class="font-medium underline">Lacak Booking</a>.
                    </p>
                @endif
            </div>

        @elseif ($booking->status === 'cancelled')

            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
                Booking ini telah dibatalkan. Stok alat sudah dilepas kembali.
            </div>

        @endif

        {{-- Detail --}}
        <dl class="mt-6 grid gap-4 rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm sm:grid-cols-2">
            <div><dt class="text-gray-500">Nama</dt><dd class="font-medium">{{ $booking->customer_name }}</dd></div>
            <div><dt class="text-gray-500">Kontak</dt><dd class="font-medium">{{ $booking->customer_email }}<br>{{ $booking->customer_phone }}</dd></div>
            <div><dt class="text-gray-500">Tanggal sewa</dt>
                <dd class="font-medium">{{ $booking->start_date->translatedFormat('d F Y') }} &ndash; {{ $booking->end_date->translatedFormat('d F Y') }}</dd></div>
            <div><dt class="text-gray-500">Durasi</dt><dd class="font-medium">{{ $booking->total_days }} hari</dd></div>
            @if ($booking->notes)
                <div class="sm:col-span-2"><dt class="text-gray-500">Catatan</dt><dd class="font-medium">{{ $booking->notes }}</dd></div>
            @endif
        </dl>

        {{-- Rincian alat --}}
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Alat</th>
                        <th class="px-4 py-3 text-center">Jumlah</th>
                        <th class="px-4 py-3 text-right">Tarif / hari</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($booking->items as $line)
                        <tr>
                            <td class="px-4 py-3">
                                {{ $line->item->name }}
                                @if ($line->package)
                                    <span class="block text-xs text-gray-500">dari {{ $line->package->name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">{{ $line->quantity }}</td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Format::rupiah($line->daily_rate) }}</td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Format::rupiah($line->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right text-gray-600">Total sewa</td>
                        <td class="px-4 py-2 text-right">{{ \App\Support\Format::rupiah($booking->total_amount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right text-gray-600">Deposit (dikembalikan setelah alat kembali)</td>
                        <td class="px-4 py-2 text-right">{{ \App\Support\Format::rupiah($booking->deposit_amount) }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td colspan="3" class="px-4 py-3 text-right">Total pembayaran</td>
                        <td class="px-4 py-3 text-right text-emerald-700">{{ \App\Support\Format::rupiah($booking->total_amount + $booking->deposit_amount) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <x-button :href="route('catalog.index')" variant="secondary">Kembali ke Katalog</x-button>
            <x-button :href="route('home')" variant="ghost">Beranda</x-button>
        </div>
    </div>
</x-layouts.app>
