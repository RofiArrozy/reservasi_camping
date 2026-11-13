{{-- Gambar alat. Jika belum ada foto, tampil ikon sesuai kategori. --}}
@props(['item'])

@php
    $icons = [
        'Tenda'              => '⛺',
        'Perlengkapan Tidur' => '🛏️',
        'Alat Masak'         => '🍳',
        'Penerangan'         => '🔦',
        'Tas & Carrier'      => '🎒',
        'Furnitur Camping'   => '🪑',
        'Aksesoris'          => '🥾',
    ];
    $icon = $icons[$item->category->name ?? ''] ?? '🏕️';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-center overflow-hidden bg-emerald-50']) }}>
    @if ($item->image_path)
        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="h-full w-full object-cover" loading="lazy">
    @else
        <span class="text-5xl" aria-hidden="true">{{ $icon }}</span>
    @endif
</div>
