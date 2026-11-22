<x-layouts.dashboard title="Profil Saya" heading="Profil Saya">

    {{-- Data pribadi --}}
    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Data pribadi</h2>

        <form method="POST" action="{{ route('user.profile.update') }}" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <x-input label="Nama lengkap" name="name" :value="$user->name" required autocomplete="name" />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input label="Email" name="email" type="email" :value="$user->email" required autocomplete="email" />
                <x-input label="Nomor telepon" name="phone" type="tel" :value="$user->phone" required autocomplete="tel" />
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Alamat <span class="font-normal text-gray-500">(opsional)</span></label>
                <textarea id="address" name="address" rows="3" maxlength="500"
                          class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('address', $user->address) }}</textarea>
                @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <x-button type="submit">Simpan Perubahan</x-button>
        </form>
    </section>

    {{-- Ganti password --}}
    <section class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Ganti password</h2>

        <form method="POST" action="{{ route('user.profile.password') }}" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <x-input label="Password saat ini" name="current_password" type="password" required autocomplete="current-password" />
            <x-input label="Password baru" name="password" type="password" required autocomplete="new-password" />
            <p class="-mt-2 text-xs text-gray-500">Minimal 8 karakter dan mengandung huruf serta angka.</p>
            <x-input label="Konfirmasi password baru" name="password_confirmation" type="password" required autocomplete="new-password" />

            <x-button type="submit">Ubah Password</x-button>
        </form>
    </section>

</x-layouts.dashboard>
