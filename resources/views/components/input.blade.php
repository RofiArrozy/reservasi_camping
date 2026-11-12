{{-- Contoh: <x-input label="Email" name="email" type="email" required /> --}}
@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{-- Password tidak pernah diisi ulang dari old input demi keamanan --}}
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @required($required)
        {{ $attributes->merge([
            'class' => 'mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 '
                . ($errors->has($name)
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                    : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500'),
        ]) }}
    >

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
