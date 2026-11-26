{{-- Diagram batang horizontal. data: [['label' => '...', 'value' => 3], ...] --}}
@props(['data', 'unit' => '', 'empty' => 'Belum ada data pada periode ini.'])

@php $max = max(collect($data)->max('value') ?? 0, 1); @endphp

@if (empty($data))
    <p class="py-10 text-center text-sm text-gray-500">{{ $empty }}</p>
@else
    <ul class="space-y-3">
        @foreach ($data as $row)
            <li>
                <div class="flex items-baseline justify-between gap-3 text-sm">
                    <span class="truncate">{{ $row['label'] }}</span>
                    <span class="shrink-0 font-medium">{{ $row['value'] }} {{ $unit }}</span>
                </div>
                <div class="mt-1 h-2.5 rounded-full bg-gray-100">
                    <div class="h-2.5 rounded-full bg-emerald-500" style="width: {{ max(3, (int) round($row['value'] / $max * 100)) }}%"></div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
