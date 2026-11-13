<x-layouts.app :title="$package->name">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">

        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('catalog.index') }}" class="hover:text-emerald-700">Katalog</a>
            <span class="mx-1">/</span>
            <span class="text-gray-800">{{ $package->name }}</span>
        </nav>

        <div class="mt-6 flex flex-wrap items-start justify-between gap-3">
            <h1 class="text-3xl font-bold">{{ $package->name }}</h1>
            @if ($package->discount_percent > 0)
                <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">Hemat {{ $package->discount_percent }}%</span>
            @endif
        </div>
        <p class="mt-3 text-gray-600">{{ $package->description }}</p>

        {{-- Isi paket --}}
        <h2 class="mt-8 text-lg font-semibold">Isi Paket</h2>
        <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Alat</th>
                        <th class="px-4 py-3 text-center">Jumlah</th>
                        <th class="px-4 py-3 text-right">Tarif / hari</th>
                        <th class="px-4 py-3 text-right">Subtotal / hari</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($package->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('catalog.items.show', $item) }}" class="font-medium hover:text-emerald-700">{{ $item->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-center">{{ $item->pivot->quantity }}</td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Format::rupiah($item->daily_rate) }}</td>
                            <td class="px-4 py-3 text-right">{{ \App\Support\Format::rupiah($item->daily_rate * $item->pivot->quantity) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-sm">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right text-gray-600">Total normal</td>
                        <td class="px-4 py-3 text-right">{{ \App\Support\Format::rupiah($package->normal_daily_price) }}</td>
                    </tr>
                    @if ($package->discount_percent > 0)
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-gray-600">Diskon paket ({{ $package->discount_percent }}%)</td>
                            <td class="px-4 py-3 text-right text-amber-700">- {{ \App\Support\Format::rupiah($package->normal_daily_price - $package->daily_price) }}</td>
                        </tr>
                    @endif
                    <tr class="font-bold">
                        <td colspan="3" class="px-4 py-3 text-right">Harga paket / hari</td>
                        <td class="px-4 py-3 text-right text-emerald-700">{{ \App\Support\Format::rupiah($package->daily_price) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if (Route::has('booking.create'))
            <div class="mt-6">
                <x-button :href="route('booking.create', ['package' => $package->slug])">Pesan Paket Ini</x-button>
            </div>
        @endif

    </div>
</x-layouts.app>
