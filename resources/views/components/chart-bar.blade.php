{{-- Diagram batang vertikal. data: [['label' => '...', 'value' => 3], ...] --}}
@props(['data', 'empty' => 'Belum ada data pada periode ini.'])

@php
    $max   = max(collect($data)->max('value') ?? 0, 1);
    $total = collect($data)->sum('value');
@endphp

@if ($total === 0)
    <p class="py-10 text-center text-sm text-gray-500">{{ $empty }}</p>
@else
    <div class="overflow-x-auto pb-1">
        <div class="flex min-w-max items-end justify-between gap-3 sm:min-w-0">
            @foreach ($data as $row)
                <div class="flex flex-1 flex-col items-center justify-end" style="min-width: 2.75rem">
                    <span class="mb-1 text-xs font-semibold text-gray-700">{{ $row['value'] }}</span>
                    <div class="w-full max-w-[3rem] rounded-t bg-emerald-500"
                         style="height: {{ $row['value'] > 0 ? max(6, (int) round($row['value'] / $max * 128)) : 2 }}px"
                         title="{{ $row['label'] }}: {{ $row['value'] }}"></div>
                    <span class="mt-1 whitespace-nowrap text-[11px] text-gray-500">{{ $row['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
