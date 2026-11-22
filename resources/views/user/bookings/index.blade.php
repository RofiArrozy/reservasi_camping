<x-layouts.dashboard title="Riwayat Sewa" heading="Riwayat Sewa">

    @if ($bookings->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
            <p>Anda belum pernah menyewa alat.</p>
            <div class="mt-4"><x-button :href="route('booking.create')">Pesan Sekarang</x-button></div>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($bookings as $booking)
                @php $latestRequest = $booking->cancellationRequests->sortByDesc('created_at')->first(); @endphp

                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('user.bookings.show', $booking) }}" class="text-lg font-semibold tracking-wide hover:text-emerald-700">
                                {{ $booking->booking_code }}
                            </a>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $booking->start_date->translatedFormat('d M Y') }} &ndash; {{ $booking->end_date->translatedFormat('d M Y') }}
                                ({{ $booking->total_days }} hari) &middot; {{ $booking->items_count }} jenis alat
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-booking-status :booking="$booking" />
                            @if ($latestRequest)
                                <x-cancellation-status :status="$latestRequest->status" />
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-gray-600">
                            Total <span class="font-semibold text-gray-900">{{ \App\Support\Format::rupiah($booking->total_amount) }}</span>
                            <span class="text-gray-400">+ deposit {{ \App\Support\Format::rupiah($booking->deposit_amount) }}</span>
                        </p>
                        <x-button :href="route('user.bookings.show', $booking)" variant="secondary">Detail</x-button>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $bookings->links() }}</div>
    @endif

</x-layouts.dashboard>
