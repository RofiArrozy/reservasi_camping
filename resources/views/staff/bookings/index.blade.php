<x-layouts.dashboard title="Booking" heading="Manajemen Booking">

    @php
        $statusLabels = [
            'pending' => 'Menunggu Konfirmasi', 'confirmed' => 'Dikonfirmasi', 'picked_up' => 'Sedang Disewa',
            'completed' => 'Selesai', 'cancelled' => 'Dibatalkan',
        ];
        $payLabels = ['unpaid' => 'Belum dibayar', 'paid' => 'Lunas', 'refunded' => 'Dikembalikan'];
    @endphp

    {{-- Filter --}}
    <form method="GET" action="{{ route('staff.bookings.index') }}"
          class="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_14rem_auto]">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari kode, nama, email, atau telepon..."
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">Semua status</option>
            @foreach ($statusLabels as $key => $label)
                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <x-button type="submit">Cari</x-button>
            @if ($q !== '' || $status)
                <x-button :href="route('staff.bookings.index')" variant="ghost">Reset</x-button>
            @endif
        </div>
    </form>

    @if ($bookings->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
            Tidak ada booking yang cocok.
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Penyewa</th>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3 text-right">Total sewa</th>
                        <th class="px-4 py-3">Pembayaran</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($bookings as $booking)
                        @php $late = $booking->status === 'picked_up' && $booking->end_date->lt(today()); @endphp
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 font-medium">
                                <a href="{{ route('staff.bookings.show', $booking) }}" class="hover:text-emerald-700">{{ $booking->booking_code }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $booking->customer_name }}<span class="block text-xs text-gray-500">{{ $booking->customer_phone }}</span></td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $booking->start_date->translatedFormat('d M') }} &ndash; {{ $booking->end_date->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ \App\Support\Format::rupiah($booking->total_amount) }}</td>
                            <td class="px-4 py-3">{{ $payLabels[$booking->payment_status] ?? $booking->payment_status }}</td>
                            <td class="px-4 py-3">
                                <x-booking-status :booking="$booking" />
                                @if ($late)<span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">Terlambat</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('staff.bookings.show', $booking) }}" class="text-sm font-medium text-emerald-700 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $bookings->links() }}</div>
    @endif

</x-layouts.dashboard>
