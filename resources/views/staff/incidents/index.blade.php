<x-layouts.dashboard title="Alat Rusak/Hilang" heading="Alat Rusak / Hilang">

    @php
        $tabs = [
            'open'   => ['Belum lunas', $counts['open']],
            'paid'   => ['Lunas', $counts['paid']],
            'waived' => ['Dibebaskan', $counts['waived']],
            'all'    => ['Semua', $counts['all']],
        ];
        $labels = ['reported' => 'Dilaporkan', 'billed' => 'Ditagih', 'paid' => 'Lunas', 'waived' => 'Dibebaskan'];
    @endphp

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-gray-500">Total tagihan ganti rugi belum lunas</p>
        <p class="mt-1 text-3xl font-bold {{ $openSum > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ \App\Support\Format::rupiah($openSum) }}</p>
        <p class="mt-1 text-xs text-gray-500">Insiden dicatat dari halaman detail booking setelah alat diambil.</p>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($tabs as $key => [$label, $count])
            <a href="{{ route('staff.incidents.index', ['tab' => $key]) }}"
               @class([
                   'rounded-lg px-3 py-2 text-sm font-medium transition',
                   'bg-emerald-600 text-white' => $tab === $key,
                   'border border-gray-200 bg-white text-gray-600 hover:bg-gray-50' => $tab !== $key,
               ])>{{ $label }}
                <span @class(['ml-1 rounded-full px-2 py-0.5 text-xs', 'bg-white/20' => $tab === $key, 'bg-gray-100' => $tab !== $key])>{{ $count }}</span>
            </a>
        @endforeach
    </div>

    @if ($incidents->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
            Tidak ada insiden pada kategori ini.
        </div>
    @else
        <div class="mt-6 space-y-3">
            @foreach ($incidents as $incident)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">
                                {{ $incident->type === 'lost' ? 'Hilang' : 'Rusak' }}: {{ $incident->quantity }}× {{ $incident->item->name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Booking
                                <a href="{{ route('staff.bookings.show', $incident->booking_id) }}" class="font-medium text-emerald-700 hover:underline">{{ $incident->booking->booking_code }}</a>
                                &middot; {{ $incident->booking->customer_name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold">{{ \App\Support\Format::rupiah($incident->replacement_cost) }}</p>
                            <span class="text-xs font-semibold text-gray-600">{{ $labels[$incident->status] ?? $incident->status }}</span>
                        </div>
                    </div>

                    @if ($incident->description)
                        <p class="mt-2 rounded-lg bg-gray-50 p-3 text-sm">{{ $incident->description }}</p>
                    @endif

                    <p class="mt-2 text-xs text-gray-500">
                        Dicatat {{ $incident->created_at->translatedFormat('d M Y H:i') }}
                        @if ($incident->reporter) oleh {{ $incident->reporter->name }} @endif
                        @if ($incident->resolved_at) &middot; diselesaikan {{ $incident->resolved_at->translatedFormat('d M Y') }} @endif
                    </p>

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
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $incidents->links() }}</div>
    @endif

</x-layouts.dashboard>
