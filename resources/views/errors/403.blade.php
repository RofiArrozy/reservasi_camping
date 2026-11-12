<x-layouts.app title="Akses Ditolak">
    <div class="mx-auto max-w-xl px-4 py-20 text-center">
        <p class="text-6xl font-bold text-emerald-700">403</p>
        <h1 class="mt-4 text-2xl font-bold">Akses Ditolak</h1>
        <p class="mt-2 text-gray-600">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}
        </p>
        <div class="mt-6">
            <x-button :href="route('home')">Kembali ke Beranda</x-button>
        </div>
    </div>
</x-layouts.app>
