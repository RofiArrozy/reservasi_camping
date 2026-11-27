<x-layouts.dashboard title="Dashboard Admin" heading="Dashboard Admin">

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Halo, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-sm text-gray-600">
            Anda memiliki seluruh fitur Staff, ditambah pengelolaan akun dan pemantauan log aktivitas.
        </p>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
            <p class="text-sm text-gray-500">Total akun</p>
            <p class="mt-1 text-3xl font-bold">{{ $stats['admin'] + $stats['staff'] + $stats['user'] }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $stats['admin'] }} admin &middot; {{ $stats['staff'] }} staff &middot; {{ $stats['user'] }} pengguna</p>
        </a>

        <a href="{{ route('admin.users.index', ['status' => 'nonaktif']) }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
            <p class="text-sm text-gray-500">Akun nonaktif</p>
            <p class="mt-1 text-3xl font-bold {{ $stats['inactive'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $stats['inactive'] }}</p>
            <p class="mt-1 text-xs text-gray-500">Klik untuk melihat</p>
        </a>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total booking</p>
            <p class="mt-1 text-3xl font-bold">{{ $stats['bookings'] }}</p>
            <p class="mt-1 text-xs text-gray-500">Pendapatan {{ \App\Support\Format::rupiah($stats['income']) }} (simulasi)</p>
        </div>

        <a href="{{ route('admin.activity-logs.index') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md">
            <p class="text-sm text-gray-500">Aktivitas 24 jam terakhir</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $stats['logs24'] }}</p>
            <p class="mt-1 text-xs text-gray-500">Klik untuk membuka log</p>
        </a>
    </div>

    <section class="mt-6 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold">Aktivitas terbaru</h2>
            <a href="{{ route('admin.activity-logs.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Lihat semua &rarr;</a>
        </div>

        @if ($recentLogs->isEmpty())
            <p class="p-5 text-sm text-gray-500">Belum ada aktivitas tercatat.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($recentLogs as $log)
                    <li class="flex flex-wrap items-start justify-between gap-2 px-5 py-3 text-sm">
                        <div>
                            <p class="font-medium">{{ $log->description ?? $log->action }}</p>
                            <p class="text-xs text-gray-500">{{ $log->user?->name ?? 'Sistem' }} &middot; <code>{{ $log->action }}</code></p>
                        </div>
                        <span class="whitespace-nowrap text-xs text-gray-500">{{ $log->created_at->translatedFormat('d M Y H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

</x-layouts.dashboard>
