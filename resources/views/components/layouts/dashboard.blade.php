@props(['title' => null, 'heading' => null])

{{-- Layout dashboard: sidebar menu (per role) + area konten. Dibungkus layout utama. --}}
<x-layouts.app :title="$title">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="lg:flex lg:gap-8">

            <aside class="lg:w-64 lg:shrink-0">
                <x-dashboard-menu />
            </aside>

            <section class="mt-6 min-w-0 flex-1 lg:mt-0">
                @if ($heading)
                    <h1 class="mb-6 text-2xl font-bold">{{ $heading }}</h1>
                @endif

                {{ $slot }}
            </section>

        </div>
    </div>
</x-layouts.app>
