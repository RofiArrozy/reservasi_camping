<x-layouts.dashboard title="Dashboard" heading="Dashboard Saya">

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Halo, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-sm text-gray-600">Ringkasan penyewaan dan pengajuan Anda.</p>
    </div>

    {{-- Statistik --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Total sewa', $stats['total'], 'text-gray-900'],
            ['Sedang aktif', $stats['active'], 'text-emerald-700'],
            ['Menunggu pembayaran', $stats['unpaid'], 'text-amber-700'],
            ['Pembatalan diproses', $stats['cancelPending'], 'text-sky-700'],
        ] as [$label, $value, $color])
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-3xl font-bold {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- Booking terdekat --}}
    <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Booking terdekat</h2>
        @if ($upcoming)
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-semibold tracking-wide">{{ $upcoming->booking_code }}</p>
                    <p class="text-sm text-gray-600">
                        {{ $upcoming->start_date->translatedFormat('d F Y') }} &ndash; {{ $upcoming->end_date->translatedFormat('d F Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <x-booking-status :booking="$upcoming" />
                    <x-button :href="route('user.bookings.show', $upcoming)" variant="secondary">Detail</x-button>
                </div>
            </div>
        @else
            <p class="mt-2 text-sm text-gray-500">Belum ada booking mendatang.</p>
            <div class="mt-3">
                <x-button :href="route('booking.create')">Pesan Sekarang</x-button>
            </div>
        @endif
    </section>

    {{-- Riwayat terbaru --}}
    @if ($recent->isNotEmpty())
        <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Riwayat terbaru</h2>
                <a href="{{ route('user.bookings.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Lihat semua &rarr;</a>
            </div>
            <ul class="mt-3 divide-y divide-gray-100">
                @foreach ($recent as $booking)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <a href="{{ route('user.bookings.show', $booking) }}" class="font-medium hover:text-emerald-700">{{ $booking->booking_code }}</a>
                        <span class="text-gray-500">{{ $booking->start_date->translatedFormat('d M Y') }}</span>
                        <x-booking-status :booking="$booking" />
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

</x-layouts.dashboard>
