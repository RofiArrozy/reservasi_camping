@props(['item'])

<a href="{{ route('catalog.items.show', $item) }}"
   class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
    <x-item-thumb :item="$item" class="h-36" />

    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">{{ $item->category->name }}</p>
        <h3 class="mt-1 font-semibold group-hover:text-emerald-700">{{ $item->name }}</h3>
        <p class="mt-auto pt-3 font-bold text-gray-900">
            {{ \App\Support\Format::rupiah($item->daily_rate) }}
            <span class="text-xs font-normal text-gray-500">/ hari</span>
        </p>
    </div>
</a>
