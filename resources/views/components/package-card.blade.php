@props(['package'])

<div class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-2">
        <h3 class="text-lg font-semibold">{{ $package->name }}</h3>
        @if ($package->discount_percent > 0)
            <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                Hemat {{ $package->discount_percent }}%
            </span>
        @endif
    </div>

    <p class="mt-2 text-sm text-gray-600">{{ $package->description }}</p>

    <ul class="mt-3 space-y-1 text-sm text-gray-700">
        @foreach ($package->items->take(4) as $item)
            <li>• {{ $item->pivot->quantity }}× {{ $item->name }}</li>
        @endforeach
        @if ($package->items->count() > 4)
            <li class="text-gray-500">+ {{ $package->items->count() - 4 }} alat lainnya</li>
        @endif
    </ul>

    <div class="mt-auto pt-4">
        @if ($package->discount_percent > 0)
            <p class="text-xs text-gray-400 line-through">{{ \App\Support\Format::rupiah($package->normal_daily_price) }} / hari</p>
        @endif
        <p class="text-xl font-bold text-emerald-700">
            {{ \App\Support\Format::rupiah($package->daily_price) }}
            <span class="text-xs font-normal text-gray-500">/ hari</span>
        </p>

        <div class="mt-4 flex gap-2">
            <x-button :href="route('catalog.packages.show', $package)" variant="secondary" class="flex-1">Detail</x-button>
            @if (Route::has('booking.create'))
                <x-button :href="route('booking.create', ['package' => $package->slug])" class="flex-1">Pesan</x-button>
            @endif
        </div>
    </div>
</div>
