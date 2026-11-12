@php
    use Illuminate\Support\Facades\Route;

    $role = auth()->user()->role;

    // Format: [label, nama route, pola route untuk penanda menu aktif]
    $staffMenu = [
        ['Pembatalan',        'staff.cancellations.index', 'staff.cancellations.*'],
        ['Alat Camping',      'staff.items.index',         'staff.items.*'],
        ['Paket Sewa',        'staff.packages.index',      'staff.packages.*'],
        ['Artikel',           'staff.articles.index',      'staff.articles.*'],
        ['Alat Rusak/Hilang', 'staff.incidents.index',     'staff.incidents.*'],
    ];

    $menus = match ($role) {
        'admin' => array_merge(
            [
                ['Ringkasan',       'admin.dashboard', 'admin.dashboard'],
                ['Dashboard Staff', 'staff.dashboard', 'staff.dashboard'],
            ],
            $staffMenu,
            [
                ['Kelola Akun',     'admin.users.index',         'admin.users.*'],
                ['Log Aktivitas',   'admin.activity-logs.index', 'admin.activity-logs.*'],
            ]
        ),
        'staff' => array_merge(
            [['Ringkasan', 'staff.dashboard', 'staff.dashboard']],
            $staffMenu
        ),
        default => [
            ['Ringkasan',      'user.dashboard',      'user.dashboard'],
            ['Riwayat Sewa',   'user.bookings.index', 'user.bookings.*'],
            ['Profil Saya',    'user.profile.edit',   'user.profile.*'],
            ['Analitik',       'user.analytics',      'user.analytics'],
        ],
    };

    // Menu hanya tampil jika route-nya sudah dibuat.
    $menus = collect($menus)->filter(fn ($m) => Route::has($m[1]));

    $panelLabel = match ($role) {
        'admin' => 'Panel Admin',
        'staff' => 'Panel Staff',
        default => 'Akun Saya',
    };
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
    <p class="px-3 pb-2 pt-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $panelLabel }}</p>

    {{-- Mobile: menu horizontal yang bisa digeser. Desktop (lg): menu vertikal. --}}
    <nav class="flex gap-1 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible lg:pb-0" aria-label="Menu dashboard">
        @foreach ($menus as [$label, $routeName, $pattern])
            <a href="{{ route($routeName) }}"
               @class([
                   'whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium transition',
                   'bg-emerald-50 text-emerald-700' => request()->routeIs($pattern),
                   'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! request()->routeIs($pattern),
               ])>
                {{ $label }}
            </a>
        @endforeach
    </nav>
</div>
