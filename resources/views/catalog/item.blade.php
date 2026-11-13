<x-layouts.app :title="$item->name">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('catalog.index') }}" class="hover:text-emerald-700">Katalog</a>
            <span class="mx-1">/</span>
            <a href="{{ route('catalog.index', ['kategori' => $item->category->slug]) }}" class="hover:text-emerald-700">{{ $item->category->name }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-800">{{ $item->name }}</span>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-2">
            <x-item-thumb :item="$item" class="h-64 rounded-2xl border border-gray-200 sm:h-80 [&>span]:text-8xl" />

            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">{{ $item->category->name }}</p>
                <h1 class="mt-1 text-3xl font-bold">{{ $item->name }}</h1>
                <p class="mt-4 text-gray-600">{{ $item->description }}</p>

                <p class="mt-6 text-3xl font-bold text-emerald-700">
                    {{ \App\Support\Format::rupiah($item->daily_rate) }}
                    <span class="text-sm font-normal text-gray-500">/ hari</span>
                </p>

                <dl class="mt-6 divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white text-sm">
                    <div class="flex justify-between px-4 py-3">
                        <dt class="text-gray-600">Jumlah unit dimiliki</dt>
                        <dd class="font-medium">{{ $item->total_stock }} unit</dd>
                    </div>
                    <div class="flex justify-between px-4 py-3">
                        <dt class="text-gray-600">Nilai penggantian jika rusak/hilang</dt>
                        <dd class="font-medium">{{ \App\Support\Format::rupiah($item->replacement_cost) }} / unit</dd>
                    </div>
                </dl>

                <p class="mt-3 text-xs text-gray-500">
                    Ketersediaan pada tanggal tertentu diperiksa otomatis saat Anda memesan.
                </p>

                @if (Route::has('booking.create'))
                    <div class="mt-6">
                        <x-button :href="route('booking.create', ['item' => $item->slug])" class="w-full sm:w-auto">Pesan Alat Ini</x-button>
                    </div>
                @endif
            </div>
        </div>

        @if ($related->isNotEmpty())
            <h2 class="mt-14 text-xl font-bold">Alat Sejenis</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $relatedItem)
                    <x-item-card :item="$relatedItem" />
                @endforeach
            </div>
        @endif

    </div>
</x-layouts.app>
