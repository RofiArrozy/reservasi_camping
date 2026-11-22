<x-layouts.app :title="'Status ' . $booking->booking_code">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">

        <a href="{{ route('tracker.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Lacak booking lain</a>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Kode booking</p>
                <h1 class="text-2xl font-bold tracking-wider">{{ $booking->booking_code }}</h1>
            </div>
            <span @class([
                'rounded-full px-3 py-1 text-sm font-semibold',
                'bg-amber-100 text-amber-800'     => $booking->status === 'pending',
                'bg-emerald-100 text-emerald-800' => in_array($booking->status, ['confirmed', 'picked_up', 'completed']),
                'bg-red-100 text-red-800'         => $booking->status === 'cancelled',
            ])>{{ $booking->status_label }}</span>
        </div>

        {{-- Linimasa status --}}
        @if ($booking->status === 'cancelled')
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
                Booking ini telah dibatalkan dan stok alat sudah dilepas.
                @if ($booking->payment_status === 'refunded')
                    Deposit tercatat telah dikembalikan.
                @endif
            </div>
        @else
            @php
                $steps   = ['Dipesan', 'Dikonfirmasi', 'Alat diambil', 'Selesai'];
                $current = ['pending' => 0, 'confirmed' => 1, 'picked_up' => 2, 'completed' => 3][$booking->status] ?? 0;
            @endphp
            <ol class="mt-6 grid grid-cols-4 gap-2 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                @foreach ($steps as $i => $label)
                    <li class="text-center">
                        <span @class([
                            'mx-auto flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold',
                            'bg-emerald-600 text-white' => $i <= $current,
                            'bg-gray-200 text-gray-500' => $i > $current,
                        ])>{{ $i <= $current ? '✓' : $i + 1 }}</span>
                        <span class="mt-1 block text-xs {{ $i <= $current ? 'font-semibold text-emerald-700' : 'text-gray-500' }}">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
        @endif

        {{-- Menunggu pembayaran: arahkan menyelesaikan pembayaran --}}
        @if ($booking->status === 'pending' && $booking->payment_status === 'unpaid')
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                <p class="font-semibold">Booking menunggu pembayaran.</p>
                <p class="mt-1">Tiket pengambilan akan tersedia setelah pembayaran dikonfirmasi.</p>
                <div class="mt-3">
                    <x-button :href="route('booking.show', ['booking' => $booking->qr_token])">Selesaikan Pembayaran</x-button>
                </div>
            </div>
        @endif

        {{-- Tiket pengambilan: hanya untuk booking terkonfirmasi / sedang berjalan --}}
        @if (in_array($booking->status, ['confirmed', 'picked_up']))
            <section class="mt-6 overflow-hidden rounded-2xl border-2 border-emerald-600 bg-white shadow-sm">
                <div class="bg-emerald-600 px-5 py-3 text-sm font-semibold text-white">🎫 Tiket Pengambilan Alat</div>

                <div class="grid gap-6 p-5 sm:grid-cols-[1fr_auto]">
                    <div class="space-y-3 text-sm">
                        <div><p class="text-gray-500">Penyewa</p><p class="font-semibold">{{ $booking->customer_name }}</p></div>
                        <div><p class="text-gray-500">Periode sewa</p>
                            <p class="font-semibold">{{ $booking->start_date->translatedFormat('d F Y') }} &ndash; {{ $booking->end_date->translatedFormat('d F Y') }} ({{ $booking->total_days }} hari)</p></div>
                        <div><p class="text-gray-500">Lokasi pengambilan</p><p class="font-semibold">Gudang Alat, Surakarta</p></div>
                    </div>

                    <div class="text-center">
                        <x-qr-dummy :value="$booking->qr_token" :size="150" class="mx-auto rounded-lg border border-gray-200" />
                        <p class="mt-1 text-xs text-gray-500">QR dummy (ilustrasi)</p>
                    </div>
                </div>

                {{-- Checklist serah terima --}}
                <div class="border-t border-dashed border-gray-300 p-5">
                    <p class="text-sm font-semibold">Checklist serah terima</p>
                    <p class="text-xs text-gray-500">Periksa bersama petugas saat alat diambil dan dikembalikan.</p>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($booking->items as $line)
                            <li>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    {{ $line->quantity }}× {{ $line->item->name }}
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        {{-- Tagihan ganti rugi (alat rusak/hilang) --}}
        @if ($booking->incidents->isNotEmpty())
            @php
                $labels  = ['reported' => 'Dilaporkan', 'billed' => 'Ditagih', 'paid' => 'Lunas', 'waived' => 'Dibebaskan'];
                $unpaid  = $booking->incidents->whereIn('status', ['reported', 'billed'])->sum('replacement_cost');
            @endphp
            <section class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 text-sm">
                <p class="font-semibold text-red-900">Penggantian alat rusak / hilang</p>
                <ul class="mt-3 space-y-2">
                    @foreach ($booking->incidents as $incident)
                        <li class="rounded-lg bg-white p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-medium">
                                    {{ $incident->type === 'lost' ? 'Hilang' : 'Rusak' }}:
                                    {{ $incident->quantity }}× {{ $incident->item->name }}
                                </span>
                                <span class="text-xs font-semibold text-gray-600">{{ $labels[$incident->status] ?? $incident->status }}</span>
                            </div>
                            @if ($incident->description)
                                <p class="mt-1 text-xs text-gray-600">{{ $incident->description }}</p>
                            @endif
                            <p class="mt-1 font-semibold">{{ \App\Support\Format::rupiah($incident->replacement_cost) }}</p>
                        </li>
                    @endforeach
                </ul>
                @if ($unpaid > 0)
                    <p class="mt-3 font-semibold text-red-900">Sisa yang harus dibayar: {{ \App\Support\Format::rupiah($unpaid) }}</p>
                @endif
            </section>
        @endif

        {{-- Rincian --}}
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Alat</th>
                        <th class="px-4 py-3 text-center">Jumlah</th>
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
                            <td class="px-4 py-3 text-right">{{ \App\Support\Format::rupiah($line->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr><td colspan="2" class="px-4 py-2 text-right text-gray-600">Deposit</td>
                        <td class="px-4 py-2 text-right">{{ \App\Support\Format::rupiah($booking->deposit_amount) }}</td></tr>
                    <tr class="font-bold"><td colspan="2" class="px-4 py-3 text-right">Total pembayaran</td>
                        <td class="px-4 py-3 text-right text-emerald-700">{{ \App\Support\Format::rupiah($booking->total_amount + $booking->deposit_amount) }}</td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-layouts.app>
