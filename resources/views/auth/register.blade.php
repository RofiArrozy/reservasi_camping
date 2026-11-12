<x-layouts.app title="Daftar">
    <div class="mx-auto max-w-md px-4 py-12">

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-bold">Buat Akun</h1>
            <p class="mt-1 text-sm text-gray-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:underline">Masuk</a>
            </p>

            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf

                <x-input label="Nama lengkap" name="name" required autofocus autocomplete="name" />
                <x-input label="Email" name="email" type="email" required autocomplete="email" />
                <x-input label="Nomor telepon" name="phone" type="tel" placeholder="081234567890" required autocomplete="tel" />
                <x-input label="Password" name="password" type="password" required autocomplete="new-password" />
                <p class="-mt-2 text-xs text-gray-500">Minimal 8 karakter dan mengandung huruf serta angka.</p>
                <x-input label="Konfirmasi password" name="password_confirmation" type="password" required autocomplete="new-password" />

                <x-button type="submit" class="w-full">Daftar</x-button>
            </form>
        </div>

    </div>
</x-layouts.app>
