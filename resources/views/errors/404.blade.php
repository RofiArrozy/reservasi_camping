<x-layouts.app title="Halaman Tidak Ditemukan">
    <div class="mx-auto max-w-xl px-4 py-20 text-center">
        <p class="text-6xl font-bold text-emerald-600">404</p>
        <h1 class="mt-4 text-2xl font-bold">Halaman Tidak Ditemukan</h1>
        <p class="mt-2 text-gray-600">Halaman yang Anda cari tidak ada atau sudah dipindahkan.</p>
        <div class="mt-6 flex justify-center gap-3">
            <x-button :href="route('home')">Kembali ke Beranda</x-button>
        </div>
    </div>
</x-layouts.app>
