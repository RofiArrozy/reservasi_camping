@props(['status'])

@php
    [$label, $color] = match ($status) {
        'approved' => ['Pembatalan disetujui', 'bg-emerald-100 text-emerald-800'],
        'rejected' => ['Pembatalan ditolak', 'bg-red-100 text-red-800'],
        default    => ['Pembatalan menunggu keputusan', 'bg-amber-100 text-amber-800'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-block rounded-full px-3 py-1 text-xs font-semibold ' . $color]) }}>{{ $label }}</span>
