<x-layouts.app title="Lacak Booking">
    <div class="mx-auto max-w-md px-4 py-12">

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-bold">Lacak Booking</h1>
            <p class="mt-1 text-sm text-gray-600">
                Masukkan kode booking beserta email atau nomor telepon yang Anda pakai saat memesan.
            </p>

            <form method="POST" action="{{ route('tracker.search') }}" class="mt-6 space-y-4">
                @csrf
                <x-input label="Kode booking" name="code" placeholder="CMP-20261201-ABCD" required autofocus autocomplete="off" />
                <x-input label="Email atau nomor telepon" name="contact" placeholder="email@contoh.com atau 081234567890" required autocomplete="off" />
                <x-button type="submit" class="w-full">Lacak</x-button>
            </form>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">
            Sudah punya akun? Riwayat sewa juga tersedia di dashboard setelah masuk.
        </p>
    </div>
</x-layouts.app>
