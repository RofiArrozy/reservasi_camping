<x-layouts.app title="Masuk">
    <div class="mx-auto max-w-md px-4 py-12">

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-bold">Masuk</h1>
            <p class="mt-1 text-sm text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-medium text-emerald-700 hover:underline">Daftar sekarang</a>
            </p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <x-input label="Email" name="email" type="email" required autofocus autocomplete="email" />
                <x-input label="Password" name="password" type="password" required autocomplete="current-password" />

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    Ingat saya
                </label>

                <x-button type="submit" class="w-full">Masuk</x-button>
            </form>
        </div>

        {{-- Panel akun demo (hapus jika tidak diperlukan) --}}
        <div class="mt-6 rounded-xl border border-dashed border-emerald-300 bg-emerald-50 p-4 text-sm">
            <p class="font-semibold text-emerald-800">Akun demo</p>
            <ul class="mt-2 space-y-1 text-emerald-900">
                <li><span class="font-medium">Admin:</span> admin@demo.test / Admin#2026</li>
                <li><span class="font-medium">Staff:</span> staff@demo.test / Staff#2026</li>
                <li><span class="font-medium">User:</span> user@demo.test / User#2026</li>
            </ul>
        </div>

    </div>
</x-layouts.app>
