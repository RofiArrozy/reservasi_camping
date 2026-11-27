<x-layouts.app title="Beranda">

    {{-- Hero --}}
    <section class="bg-emerald-700 text-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <h1 class="max-w-3xl text-3xl font-bold sm:text-5xl">Sewa Perlengkapan Camping, Tanpa Ribet</h1>
            <p class="mt-4 max-w-2xl text-emerald-50">
                Pilih alat atau paket, tentukan tanggal, lalu pesan. Ketersediaan stok dicek otomatis
                sehingga Anda tidak perlu menunggu konfirmasi hanya untuk tahu alatnya ada atau tidak.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button :href="route('catalog.index')" class="bg-white !text-emerald-700 hover:!bg-emerald-50">Lihat Katalog</x-button>
                @if (Route::has('booking.create'))
                    <x-button :href="route('booking.create')" variant="ghost" class="border border-white/60 !text-white hover:!bg-emerald-600">Pesan Sekarang</x-button>
                @endif
                <x-button href="#panduan" variant="ghost" class="!text-white hover:!bg-emerald-600">Cara Reservasi</x-button>
            </div>
        </div>
    </section>

    {{-- Keunggulan --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ([
                ['📦', 'Stok Akurat', 'Ketersediaan alat dicek berdasarkan tanggal sewa Anda, jadi pesanan tidak bentrok.'],
                ['💰', 'Harga Transparan', 'Tarif per hari dan nilai penggantian tiap alat ditampilkan jelas sebelum memesan.'],
                ['🛠️', 'Alat Terawat', 'Setiap alat diperiksa saat dikembalikan agar selalu siap dipakai penyewa berikutnya.'],
            ] as [$icon, $title, $text])
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-3xl" aria-hidden="true">{{ $icon }}</p>
                    <h2 class="mt-3 font-semibold">{{ $title }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Paket sewa --}}
    <section id="paket" class="mx-auto max-w-7xl scroll-mt-20 px-4 pb-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold">Paket Sewa</h2>
                <p class="mt-1 text-gray-600">Bundel hemat berisi alat yang biasa dipakai bersamaan.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="hidden text-sm font-medium text-emerald-700 hover:underline sm:block">Semua paket &rarr;</a>
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($packages as $package)
                <x-package-card :package="$package" />
            @empty
                <p class="text-gray-500">Belum ada paket tersedia.</p>
            @endforelse
        </div>
    </section>

    {{-- Alat populer --}}
    <section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold">Alat Terpopuler</h2>
                <p class="mt-1 text-gray-600">Paling sering disewa pelanggan kami.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Lihat semua &rarr;</a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($popularItems as $item)
                <x-item-card :item="$item" />
            @endforeach
        </div>
    </section>

    {{-- Panduan reservasi --}}
    <section id="panduan" class="scroll-mt-20 bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold">Panduan Reservasi</h2>
            <p class="mt-1 text-gray-600">Empat langkah mudah dari memilih alat sampai siap berangkat.</p>

            <ol class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Pilih alat atau paket', 'Jelajahi katalog dan tentukan alat yang Anda butuhkan.'],
                    ['Tentukan tanggal & isi data', 'Pilih tanggal sewa dan jumlah unit. Sistem memeriksa stok otomatis.'],
                    ['Simpan kode booking', 'Anda mendapat kode booking untuk melacak status dan menampilkan tiket.'],
                    ['Ambil alat & berangkat', 'Tunjukkan kode booking atau QR di lokasi, lalu ambil perlengkapan Anda.'],
                ] as $i => [$title, $text])
                    <li class="rounded-xl border border-gray-200 p-5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">{{ $i + 1 }}</span>
                        <h3 class="mt-3 font-semibold">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>

            {{-- Ketentuan penting --}}
            <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                <p class="font-semibold">Ketentuan penting</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Alat yang rusak atau hilang selama masa sewa wajib diganti sesuai nilai penggantian yang tertera pada tiap alat.</li>
                    <li>Pembatalan diajukan lewat dashboard akun dan diputuskan oleh staf. Jika disetujui, stok dilepas dan deposit dicatat dikembalikan.</li>
                    <li>Pembayaran pada aplikasi ini bersifat simulasi (sandbox), tanpa transaksi uang sungguhan.</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- Ajakan kontak --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-emerald-700 px-6 py-10 text-center text-white sm:px-12">
            <h2 class="text-2xl font-bold">Masih ada pertanyaan?</h2>
            <p class="mx-auto mt-2 max-w-xl text-emerald-50">Hubungi kami lewat WhatsApp untuk konsultasi kebutuhan alat atau jadwal sewa.</p>
            <div class="mt-6">
                <x-button href="https://wa.me/6281200000001" target="_blank" rel="noopener" class="bg-white !text-emerald-700 hover:!bg-emerald-50">
                    Chat WhatsApp
                </x-button>
            </div>
        </div>
    </section>

</x-layouts.app>
