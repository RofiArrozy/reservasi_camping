<x-layouts.dashboard title="Alat Camping" heading="Alat Camping">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Kelola katalog alat: kategori, stok, tarif harian, dan nilai penggantian.</p>
        <div class="flex gap-2">
            <x-button :href="route('staff.categories.index')" variant="secondary">Kelola Kategori</x-button>
            <x-button :href="route('staff.items.create')">+ Tambah Alat</x-button>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('staff.items.index') }}"
          class="mt-4 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-[1fr_12rem_10rem_auto]">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama alat..."
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <select name="kategori" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" @selected(($filters['kategori'] ?? null) === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">Semua status</option>
            <option value="aktif" @selected(($filters['status'] ?? null) === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(($filters['status'] ?? null) === 'nonaktif')>Nonaktif</option>
        </select>
        <div class="flex gap-2">
            <x-button type="submit">Cari</x-button>
            @if (filled($filters['q'] ?? null) || filled($filters['kategori'] ?? null) || filled($filters['status'] ?? null))
                <x-button :href="route('staff.items.index')" variant="ghost">Reset</x-button>
            @endif
        </div>
    </form>

    @if ($items->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">Tidak ada alat yang cocok.</div>
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Alat</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        <th class="px-4 py-3 text-right">Tarif / hari</th>
                        <th class="px-4 py-3 text-right">Ganti rugi</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <x-item-thumb :item="$item" class="h-10 w-10 shrink-0 rounded-lg [&>span]:text-xl" />
                                    <span class="font-medium">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $item->category->name }}</td>
                            <td class="px-4 py-3 text-center">{{ $item->total_stock }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ \App\Support\Format::rupiah($item->daily_rate) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ \App\Support\Format::rupiah($item->replacement_cost) }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $item->is_active,
                                    'bg-gray-100 text-gray-600' => ! $item->is_active,
                                ])>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('staff.items.edit', $item) }}" class="font-medium text-emerald-700 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('staff.items.destroy', $item) }}" class="ml-3 inline"
                                      onsubmit="return confirm('Hapus alat {{ addslashes($item->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $items->links() }}</div>
    @endif

</x-layouts.dashboard>
