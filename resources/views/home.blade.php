{{-- Halaman sementara untuk menguji layout. Akan diganti landing page sungguhan di Fase 3. --}}
<x-layouts.app title="Beranda">

    <section class="bg-emerald-700 text-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold sm:text-4xl">Sewa Perlengkapan Camping, Tanpa Ribet</h1>
            <p class="mt-4 max-w-2xl text-emerald-50">
                Pilih alat atau paket, tentukan tanggal, dan pesan. Stok dicek otomatis.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <x-button href="#paket" class="bg-white !text-emerald-700 hover:!bg-emerald-50">Lihat Paket</x-button>
                <x-button href="#panduan" variant="ghost" class="!text-white hover:!bg-emerald-600">Panduan Reservasi</x-button>
            </div>
        </div>
    </section>

    <section id="paket" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold">Paket Sewa</h2>
        <p class="mt-1 text-gray-600">Placeholder: daftar paket sungguhan menyusul di Fase 3.</p>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (['Paket Solo Hiking', 'Paket Camping Berdua', 'Paket Camping Keluarga'] as $name)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold">{{ $name }}</h3>
                    <p class="mt-2 text-sm text-gray-600">Deskripsi singkat paket.</p>
                    <x-button class="mt-4">Pesan</x-button>
                </div>
            @endforeach
        </div>
    </section>

    <section id="panduan" class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold">Panduan Reservasi</h2>
        <p class="mt-1 text-gray-600">Placeholder: langkah-langkah reservasi menyusul di Fase 3.</p>
    </section>

</x-layouts.app>
