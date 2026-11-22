<x-layouts.app title="Terlalu Banyak Permintaan">
    <div class="mx-auto max-w-xl px-4 py-20 text-center">
        <p class="text-6xl font-bold text-emerald-700">429</p>
        <h1 class="mt-4 text-2xl font-bold">Terlalu Banyak Percobaan</h1>
        <p class="mt-2 text-gray-600">Anda terlalu sering mengirim permintaan. Tunggu sekitar satu menit, lalu coba lagi.</p>
        <div class="mt-6">
            <x-button :href="route('home')">Kembali ke Beranda</x-button>
        </div>
    </div>
</x-layouts.app>
