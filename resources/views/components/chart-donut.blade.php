{{-- Diagram donat. data: [['label' => '...', 'value' => 3, 'color' => '#10b981'], ...] --}}
@props(['data', 'empty' => 'Belum ada data pada periode ini.'])

@php
    $total = collect($data)->sum('value');
    $acc   = 0;
    $stops = [];
    foreach ($data as $row) {
        $start   = $acc;
        $acc    += $total > 0 ? $row['value'] / $total * 100 : 0;
        $stops[] = $row['color'] . ' ' . round($start, 2) . '% ' . round($acc, 2) . '%';
    }
@endphp

@if ($total === 0)
    <p class="py-10 text-center text-sm text-gray-500">{{ $empty }}</p>
@else
    <div class="flex flex-wrap items-center gap-6">
        <div class="relative h-36 w-36 shrink-0 rounded-full"
             style="background: conic-gradient({{ implode(', ', $stops) }})"
             role="img" aria-label="Diagram donat, total {{ $total }}">
            <div class="absolute inset-5 flex items-center justify-center rounded-full bg-white">
                <span class="text-2xl font-bold">{{ $total }}</span>
            </div>
        </div>

        <ul class="space-y-2 text-sm">
            @foreach ($data as $row)
                <li class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full" style="background: {{ $row['color'] }}"></span>
                    <span>{{ $row['label'] }}: <span class="font-semibold">{{ $row['value'] }}</span></span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
