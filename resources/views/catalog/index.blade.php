<x-layouts.app title="Katalog">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        <h1 class="text-2xl font-bold">Katalog Peralatan</h1>
        <p class="mt-1 text-gray-600">Temukan alat dan paket camping sesuai kebutuhan Anda.</p>

        {{-- Pencarian & filter (metode GET agar hasil bisa dibagikan lewat URL) --}}
        <form method="GET" action="{{ route('catalog.index') }}" class="mt-6 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_14rem_auto]">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama alat..."
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">

            <select name="kategori" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(($filters['kategori'] ?? null) === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <x-button type="submit">Cari</x-button>
                @if (filled($filters['q'] ?? null) || filled($filters['kategori'] ?? null))
                    <x-button :href="route('catalog.index')" variant="ghost">Reset</x-button>
                @endif
            </div>
        </form>

        {{-- Paket --}}
        @if ($packages->isNotEmpty())
            <h2 class="mt-10 text-xl font-bold">Paket Sewa</h2>
            <div class="mt-4 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $package)
                    <x-package-card :package="$package" />
                @endforeach
            </div>
        @endif

        {{-- Alat --}}
        <h2 class="mt-10 text-xl font-bold">Alat Satuan</h2>

        @if ($items->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                Tidak ada alat yang cocok dengan pencarian Anda.
            </div>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $item)
                    <x-item-card :item="$item" />
                @endforeach
            </div>

            <div class="mt-8">{{ $items->links() }}</div>
        @endif

    </div>
</x-layouts.app>
