@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])

@php
    $base = 'inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2';

    $styles = [
        'primary'   => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'secondary' => 'border border-emerald-600 bg-white text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-500',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'ghost'     => 'text-gray-700 hover:bg-gray-100 focus:ring-gray-400',
    ];

    $classes = $base . ' ' . ($styles[$variant] ?? $styles['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
