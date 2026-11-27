{{-- Form bersama tambah & edit akun. Variabel: $user, $action, $method, $submit --}}
@php $isSelf = $user->exists && $user->is(auth()->user()); @endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Data akun</h2>
        <div class="mt-4 space-y-4">
            <x-input label="Nama lengkap" name="name" :value="$user->name" required />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input label="Email" name="email" type="email" :value="$user->email" required />
                <x-input label="Nomor telepon" name="phone" type="tel" :value="$user->phone" placeholder="081234567890" />
            </div>
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Alamat <span class="font-normal text-gray-500">(opsional)</span></label>
                <textarea id="address" name="address" rows="2" maxlength="500"
                          class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('address', $user->address) }}</textarea>
                @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Peran & status</h2>
        <div class="mt-4 space-y-4">
            <div class="sm:w-64">
                <label for="role" class="block text-sm font-medium text-gray-700">Peran <span class="text-red-500">*</span></label>
                <select id="role" name="role" required @disabled($isSelf)
                        class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1 disabled:bg-gray-100 {{ $errors->has('role') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500' }}">
                    @foreach (['admin' => 'Admin', 'staff' => 'Staff', 'user' => 'Pengguna'] as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                {{-- Select yang disabled tidak terkirim; kirim nilai lewat input tersembunyi --}}
                @if ($isSelf)<input type="hidden" name="role" value="{{ $user->role }}">@endif
                @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm">
                @if ($isSelf)
                    <input type="hidden" name="is_active" value="1">
                    <input type="checkbox" checked disabled class="rounded border-gray-300 text-emerald-600">
                @else
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))
                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                @endif
                Akun aktif (dapat login)
            </label>
            @if ($isSelf)
                <p class="text-xs text-gray-500">Peran dan status akun Anda sendiri tidak dapat diubah.</p>
            @endif
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">{{ $user->exists ? 'Reset password' : 'Password' }}</h2>
        @if ($user->exists)
            <p class="text-xs text-gray-500">Kosongkan jika password tidak ingin diubah.</p>
        @endif
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <x-input :label="$user->exists ? 'Password baru' : 'Password'" name="password" type="password" :required="! $user->exists" autocomplete="new-password" />
            <x-input label="Konfirmasi password" name="password_confirmation" type="password" :required="! $user->exists" autocomplete="new-password" />
        </div>
        <p class="mt-2 text-xs text-gray-500">Minimal 8 karakter dan mengandung huruf serta angka.</p>
    </section>

    <div class="flex gap-3">
        <x-button type="submit">{{ $submit }}</x-button>
        <x-button :href="route('admin.users.index')" variant="ghost">Batal</x-button>
    </div>
</form>
