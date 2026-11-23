<x-layouts.dashboard title="Dashboard Staff" heading="Dashboard Staff">

    @php
        $payLabels = ['unpaid' => 'Belum dibayar', 'paid' => 'Lunas', 'refunded' => 'Dikembalikan'];
        $canOpen   = Route::has('staff.bookings.show');
    @endphp

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Halo, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-sm text-gray-600">
            Ringkasan operasional {{ now()->translatedFormat('l, d F Y') }}.
        </p>
    </div>

    {{-- Peringatan keterlambatan --}}
    @if ($overdue > 0)
        <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <strong>{{ $overdue }} booking terlambat dikembalikan.</strong>
            Alat sudah melewati tanggal selesai sewa tetapi belum ditandai kembali.
        </div>
    @endif

    {{-- Statistik --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Sewa berlangsung hari ini</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $activeToday->count() }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $pickupsToday }} pengambilan &middot; {{ $returnsToday }} pengembalian</p>
        </div>

        <a href="{{ Route::has('staff.cancellations.index') ? route('staff.cancellations.index') : '#' }}"
           class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
            <p class="text-sm text-gray-500">Pembatalan menunggu</p>
            <p class="mt-1 text-3xl font-bold {{ $stats['cancelPending'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $stats['cancelPending'] }}</p>
            <p class="mt-1 text-xs text-gray-500">Klik untuk meninjau</p>
        </a>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Pendapatan bulan ini (simulasi)</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ \App\Support\Format::rupiah($income['month']) }}</p>
            <p class="mt-1 text-xs text-gray-500">Total keseluruhan {{ \App\Support\Format::rupiah($income['total']) }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Ganti rugi belum lunas</p>
            <p class="mt-1 text-2xl font-bold {{ $stats['incidentSum'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ \App\Support\Format::rupiah($stats['incidentSum']) }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $stats['incidentCount'] }} kasus alat rusak/hilang</p>
        </div>
    </div>

    {{-- Sewa hari ini --}}
    <section class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">
        <h2 class="border-b border-gray-200 px-5 py-4 font-semibold">Sewa hari ini</h2>

        @if ($activeToday->isEmpty())
            <p class="p-5 text-sm text-gray-500">Tidak ada sewa yang berlangsung hari ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Penyewa</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3 text-center">Jenis alat</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($activeToday as $booking)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 font-medium">
                                    @if ($canOpen)
                                        <a href="{{ route('staff.bookings.show', $booking) }}" class="hover:text-emerald-700">{{ $booking->booking_code }}</a>
                                    @else
                                        {{ $booking->booking_code }}
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $booking->customer_name }}<span class="block text-xs text-gray-500">{{ $booking->customer_phone }}</span></td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    {{ $booking->start_date->translatedFormat('d M') }} &ndash; {{ $booking->end_date->translatedFormat('d M Y') }}
                                    @if ($booking->status === 'confirmed' && $booking->start_date->isToday())
                                        <span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">Ambil hari ini</span>
                                    @elseif ($booking->end_date->isToday())
                                        <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Kembali hari ini</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">{{ $booking->items_count }}</td>
                                <td class="px-5 py-3"><x-booking-status :booking="$booking" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Transaksi terbaru --}}
    <section class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">
        <h2 class="border-b border-gray-200 px-5 py-4 font-semibold">Transaksi terbaru</h2>

        @if ($recent->isEmpty())
            <p class="p-5 text-sm text-gray-500">Belum ada transaksi.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Penyewa</th>
                            <th class="px-5 py-3">Dibuat</th>
                            <th class="px-5 py-3 text-right">Total sewa</th>
                            <th class="px-5 py-3">Pembayaran</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recent as $booking)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 font-medium">
                                    @if ($canOpen)
                                        <a href="{{ route('staff.bookings.show', $booking) }}" class="hover:text-emerald-700">{{ $booking->booking_code }}</a>
                                    @else
                                        {{ $booking->booking_code }}
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $booking->customer_name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $booking->created_at->translatedFormat('d M Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">{{ \App\Support\Format::rupiah($booking->total_amount) }}</td>
                                <td class="px-5 py-3">{{ $payLabels[$booking->payment_status] ?? $booking->payment_status }}</td>
                                <td class="px-5 py-3"><x-booking-status :booking="$booking" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

</x-layouts.dashboard>
