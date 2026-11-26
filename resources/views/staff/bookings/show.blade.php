<x-layouts.dashboard :title="'Booking ' . $booking->booking_code" heading="Detail Booking">

    @php
        $payLabels = ['unpaid' => 'Belum dibayar', 'paid' => 'Lunas (simulasi)', 'refunded' => 'Dikembalikan'];
        $incLabels = ['reported' => 'Dilaporkan', 'billed' => 'Ditagih', 'paid' => 'Lunas', 'waived' => 'Dibebaskan'];

        $late = $booking->status === 'picked_up' && $booking->end_date->lt(today())
            ? (int) abs($booking->end_date->diffInDays(today()))
            : 0;

        // Alat unik pada booking ini (untuk form insiden)
        $bookedItems = $booking->items->groupBy('item_id')->map(fn ($g) => [
            'item' => $g->first()->item,
            'qty'  => $g->sum('quantity'),
        ]);

        $openSum  = $booking->incidents->whereIn('status', ['reported', 'billed'])->sum('replacement_cost');
        $canReport = in_array($booking->status, ['picked_up', 'completed'], true);
    @endphp

    <a href="{{ route('staff.bookings.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar booking</a>

    {{-- Header --}}
    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-bold tracking-wider">{{ $booking->booking_code }}</h2>
        <div class="flex flex-wrap gap-2">
            <x-booking-status :booking="$booking" />
            <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $payLabels[$booking->payment_status] ?? $booking->payment_status }}</span>
            @if ($late > 0)
                <span class="inline-block rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">Terlambat {{ $late }} hari</span>
            @endif
        </div>
    </div>

    {{-- Tindakan --}}
    <section class="mt-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="font-semibold">Tindakan</h3>

        @if ($booking->status === 'confirmed')
            @if ($booking->start_date->gt(today()))
                <p class="mt-2 text-sm text-gray-600">
                    Alat baru dapat diambil mulai <strong>{{ $booking->start_date->translatedFormat('d F Y') }}</strong>.
                </p>
            @else
                <p class="mt-2 text-sm text-gray-600">Pelanggan siap mengambil alat. Periksa checklist bersama pelanggan, lalu tandai.</p>
                <form method="POST" action="{{ route('staff.bookings.pickup', $booking) }}" class="mt-3"
                      onsubmit="return confirm('Tandai alat sudah diambil?')">
                    @csrf
                    <x-button type="submit">Tandai Alat Sudah Diambil</x-button>
                </form>
            @endif
        @elseif ($booking->status === 'picked_up')
            <p class="mt-2 text-sm text-gray-600">
                Alat sedang disewa sampai {{ $booking->end_date->translatedFormat('d F Y') }}.
                Saat alat kembali, periksa kondisinya. Jika ada yang rusak/hilang, catat dulu di bagian bawah, lalu tandai dikembalikan.
            </p>
            <form method="POST" action="{{ route('staff.bookings.returned', $booking) }}" class="mt-3"
                  onsubmit="return confirm('Tandai alat sudah dikembalikan? Sewa akan selesai dan stok kembali tersedia.')">
                @csrf
                <x-button type="submit">Tandai Alat Dikembalikan</x-button>
            </form>
        @elseif ($booking->status === 'pending')
            <p class="mt-2 text-sm text-gray-600">Booking menunggu pembayaran pelanggan.</p>
        @elseif ($booking->status === 'completed')
            <p class="mt-2 text-sm text-gray-600">Sewa sudah selesai. Deposit {{ \App\Support\Format::rupiah($booking->deposit_amount) }} dikembalikan kepada penyewa sesuai kondisi alat.</p>
        @else
            <p class="mt-2 text-sm text-gray-600">Booking ini dibatalkan. Tidak ada tindakan yang tersedia.</p>
        @endif
    </section>

    {{-- Penyewa & periode --}}
    <dl class="mt-4 grid gap-4 rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm sm:grid-cols-2">
        <div><dt class="text-gray-500">Penyewa</dt><dd class="font-medium">{{ $booking->customer_name }}</dd></div>
        <div><dt class="text-gray-500">Kontak</dt>
            <dd class="font-medium">{{ $booking->customer_email }}<br>
                <a href="tel:{{ $booking->customer_phone }}" class="text-emerald-700 hover:underline">{{ $booking->customer_phone }}</a></dd></div>
        <div><dt class="text-gray-500">Periode sewa</dt>
            <dd class="font-medium">{{ $booking->start_date->translatedFormat('d F Y') }} &ndash; {{ $booking->end_date->translatedFormat('d F Y') }} ({{ $booking->total_days }} hari)</dd></div>
        <div><dt class="text-gray-500">Pembayaran</dt><dd class="font-medium">{{ $booking->payment_method ?? '-' }}</dd></div>
        @if ($booking->notes)
            <div class="sm:col-span-2"><dt class="text-gray-500">Catatan pelanggan</dt><dd class="font-medium">{{ $booking->notes }}</dd></div>
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
            </tfoot>
        </table>
    </div>

    {{-- Alat rusak / hilang --}}
    <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold">Alat rusak / hilang</h3>
            @if ($openSum > 0)
                <span class="text-sm font-semibold text-red-700">Belum lunas: {{ \App\Support\Format::rupiah($openSum) }}</span>
            @endif
        </div>

        @forelse ($booking->incidents as $incident)
            <div class="mt-3 rounded-lg border border-gray-200 p-4 text-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium">{{ $incident->type === 'lost' ? 'Hilang' : 'Rusak' }}: {{ $incident->quantity }}× {{ $incident->item->name }}</span>
                    <span class="text-xs font-semibold text-gray-600">{{ $incLabels[$incident->status] ?? $incident->status }}</span>
                </div>
                @if ($incident->description)<p class="mt-1 text-xs text-gray-600">{{ $incident->description }}</p>@endif
                <p class="mt-1 font-semibold">{{ \App\Support\Format::rupiah($incident->replacement_cost) }}</p>

                @if (in_array($incident->status, ['reported', 'billed']))
                    <div class="mt-3 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('staff.incidents.update', $incident) }}" onsubmit="return confirm('Tandai tagihan ini lunas?')">
                            @csrf <input type="hidden" name="status" value="paid">
                            <x-button type="submit" variant="secondary">Tandai Lunas</x-button>
                        </form>
                        <form method="POST" action="{{ route('staff.incidents.update', $incident) }}" onsubmit="return confirm('Bebaskan tagihan ini?')">
                            @csrf <input type="hidden" name="status" value="waived">
                            <x-button type="submit" variant="ghost">Bebaskan</x-button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="mt-2 text-sm text-gray-500">Belum ada insiden pada booking ini.</p>
        @endforelse

        {{-- Form catat insiden --}}
        @if ($canReport)
            <form method="POST" action="{{ route('staff.incidents.store', $booking) }}" class="mt-5 space-y-4 border-t border-gray-200 pt-5">
                @csrf
                <h4 class="text-sm font-semibold">Catat insiden baru</h4>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="item_id" class="block text-sm font-medium text-gray-700">Alat</label>
                        <select id="item_id" name="item_id" required
                                class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1 {{ $errors->has('item_id') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500' }}">
                            <option value="">Pilih alat...</option>
                            @foreach ($bookedItems as $itemId => $row)
                                <option value="{{ $itemId }}" @selected(old('item_id') == $itemId)>
                                    {{ $row['item']->name }} (disewa {{ $row['qty'] }}, ganti rugi {{ \App\Support\Format::rupiah($row['item']->replacement_cost) }}/unit)
                                </option>
                            @endforeach
                        </select>
                        @error('item_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Jenis</label>
                        <select id="type" name="type" required
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="damaged" @selected(old('type') === 'damaged')>Rusak</option>
                            <option value="lost" @selected(old('type') === 'lost')>Hilang (mengurangi stok)</option>
                        </select>
                        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <x-input label="Jumlah unit" name="quantity" type="number" :value="1" min="1" max="99" required />

                    <div>
                        <x-input label="Biaya ganti rugi (Rp)" name="replacement_cost" type="number" min="0" placeholder="Kosongkan = otomatis" />
                        <p class="mt-1 text-xs text-gray-500">Otomatis = nilai penggantian x jumlah. Isi manual untuk kerusakan sebagian.</p>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Keterangan <span class="font-normal text-gray-500">(opsional)</span></label>
                    <textarea id="description" name="description" rows="2" maxlength="300"
                              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <x-button type="submit" variant="danger">Catat Insiden</x-button>
            </form>
        @else
            <p class="mt-4 text-xs text-gray-500">Insiden dapat dicatat setelah alat diambil.</p>
        @endif
    </section>

</x-layouts.dashboard>
