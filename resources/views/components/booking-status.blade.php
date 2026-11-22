@props(['booking'])

@php
    $color = match ($booking->status) {
        'pending'   => 'bg-amber-100 text-amber-800',
        'cancelled' => 'bg-red-100 text-red-800',
        default     => 'bg-emerald-100 text-emerald-800',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-block rounded-full px-3 py-1 text-xs font-semibold ' . $color]) }}>
    {{ $booking->status_label }}
</span>
