@php
    use Illuminate\Support\Facades\Route;

    // Tautan hanya tampil jika rutenya sudah ada, sehingga navbar tidak error
    // selama fitur terkait belum dibuat.
    $links = collect([
        ['label' => 'Beranda',       'url' => route('home')],
        ['label' => 'Paket & Alat',  'url' => Route::has('catalog.index') ? route('catalog.index') : route('home') . '#paket'],
        ['label' => 'Blog',          'url' => Route::has('blog.index') ? route('blog.index') : null],
        ['label' => 'Lacak Booking', 'url' => Route::has('tracker.index') ? route('tracker.index') : null],
        ['label' => 'Panduan',       'url' => route('home') . '#panduan'],
        ['label' => 'Kontak',        'url' => route('home') . '#kontak'],
    ])->filter(fn ($link) => $link['url'])->values();

    // Tandai menu aktif (tautan anchor '#' tidak ditandai).
    $isActive = fn (string $url) => ! str_contains($url, '#') && url()->current() === $url;

    $linkBase   = 'rounded-lg px-3 py-2 text-sm font-medium transition';
    $linkActive = 'bg-emerald-50 text-emerald-700';
    $linkIdle   = 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';
@endphp

<header class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold text-emerald-700">
            <span aria-hidden="true">⛺</span>
            <span>{{ config('app.name') }}</span>
        </a>

        {{-- Menu desktop --}}
        <div class="hidden items-center gap-1 md:flex">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}"
                   class="{{ $linkBase }} {{ $isActive($link['url']) ? $linkActive : $linkIdle }}">
                    {{ $link['label'] }}
                </a>
            @endforeach

            <div class="ml-3 flex items-center gap-2 border-l border-gray-200 pl-3">
                @auth
                    @if (Route::has('dashboard'))
                        <x-button :href="route('dashboard')" variant="secondary">Dashboard</x-button>
                    @endif
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-button type="submit" variant="ghost">Keluar</x-button>
                        </form>
                    @endif
                @else
                    @if (Route::has('login'))
                        <x-button :href="route('login')" variant="ghost">Masuk</x-button>
                    @endif
                    @if (Route::has('register'))
                        <x-button :href="route('register')">Daftar</x-button>
                    @endif
                @endauth
            </div>
        </div>

        {{-- Tombol hamburger (mobile) --}}
        <button id="nav-toggle" type="button"
                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 md:hidden"
                aria-controls="nav-menu" aria-expanded="false" aria-label="Buka menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </nav>

    {{-- Menu mobile --}}
    <div id="nav-menu" class="hidden border-t border-gray-200 bg-white px-4 pb-4 pt-2 md:hidden">
        <div class="flex flex-col gap-1">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}"
                   class="{{ $linkBase }} {{ $isActive($link['url']) ? $linkActive : $linkIdle }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="mt-3 flex flex-col gap-2 border-t border-gray-200 pt-3">
            @auth
                @if (Route::has('dashboard'))
                    <x-button :href="route('dashboard')" variant="secondary" class="w-full">Dashboard</x-button>
                @endif
                @if (Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-button type="submit" variant="ghost" class="w-full">Keluar</x-button>
                    </form>
                @endif
            @else
                @if (Route::has('login'))
                    <x-button :href="route('login')" variant="secondary" class="w-full">Masuk</x-button>
                @endif
                @if (Route::has('register'))
                    <x-button :href="route('register')" class="w-full">Daftar</x-button>
                @endif
            @endauth
        </div>
    </div>

    <script>
        // Buka/tutup menu mobile
        document.getElementById('nav-toggle')?.addEventListener('click', function () {
            const menu = document.getElementById('nav-menu');
            const open = menu.classList.toggle('hidden') === false;
            this.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    </script>
</header>
