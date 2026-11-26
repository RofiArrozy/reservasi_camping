<x-layouts.dashboard title="Paket Sewa" heading="Paket Sewa">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Bundel alat dengan diskon. Harga paket dihitung otomatis dari tarif tiap alat.</p>
        <x-button :href="route('staff.packages.create')">+ Tambah Paket</x-button>
    </div>

    <form method="GET" action="{{ route('staff.packages.index') }}" class="mt-4 flex gap-2 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama paket..."
               class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <x-button type="submit">Cari</x-button>
        @if (filled($filters['q'] ?? null))<x-button :href="route('staff.packages.index')" variant="ghost">Reset</x-button>@endif
    </form>

    @if ($packages->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">Tidak ada paket.</div>
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Paket</th>
                        <th class="px-4 py-3 text-center">Jenis alat</th>
                        <th class="px-4 py-3 text-right">Harga normal</th>
                        <th class="px-4 py-3 text-center">Diskon</th>
                        <th class="px-4 py-3 text-right">Harga paket / hari</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($packages as $package)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $package->name }}</td>
                            <td class="px-4 py-3 text-center">{{ $package->items->count() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-gray-500">{{ \App\Support\Format::rupiah($package->normal_daily_price) }}</td>
                            <td class="px-4 py-3 text-center">{{ $package->discount_percent }}%</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-emerald-700">{{ \App\Support\Format::rupiah($package->daily_price) }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $package->is_active,
                                    'bg-gray-100 text-gray-600' => ! $package->is_active,
                                ])>{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('staff.packages.edit', $package) }}" class="font-medium text-emerald-700 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('staff.packages.destroy', $package) }}" class="ml-3 inline"
                                      onsubmit="return confirm('Hapus paket {{ addslashes($package->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $packages->links() }}</div>
    @endif

</x-layouts.dashboard>
