<footer id="kontak" class="mt-12 border-t border-gray-200 bg-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3 lg:px-8">

        <div>
            <p class="flex items-center gap-2 text-lg font-bold text-emerald-700">
                <span aria-hidden="true">⛺</span> {{ config('app.name') }}
            </p>
            <p class="mt-3 text-sm text-gray-600">
                Sewa perlengkapan camping lengkap, mudah dipesan, dan harga transparan.
            </p>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-gray-900">Kontak</h2>
            <ul class="mt-3 space-y-2 text-sm text-gray-600">
                <li>Jl. Contoh No. 1, Surakarta, Jawa Tengah</li>
                <li>WhatsApp: 0812-0000-0001</li>
                <li>Email: halo@reservasicamping.test</li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-gray-900">Jam Operasional</h2>
            <ul class="mt-3 space-y-2 text-sm text-gray-600">
                <li>Senin - Jumat: 08.00 - 20.00</li>
                <li>Sabtu - Minggu: 07.00 - 21.00</li>
            </ul>
        </div>
    </div>

    <div class="border-t border-gray-100 py-4 text-center text-xs text-gray-500">
        &copy; {{ now()->year }} {{ config('app.name') }}. Pembayaran pada aplikasi ini bersifat simulasi (sandbox).
    </div>
</footer>
