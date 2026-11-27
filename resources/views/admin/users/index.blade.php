<x-layouts.dashboard title="Kelola Akun" heading="Kelola Akun">

    @php $roleLabels = ['admin' => 'Admin', 'staff' => 'Staff', 'user' => 'Pengguna']; @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Kelola akun Admin, Staff, dan Pengguna.</p>
        <x-button :href="route('admin.users.create')">+ Tambah Akun</x-button>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}"
          class="mt-4 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-[1fr_10rem_10rem_auto]">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama atau email..."
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <select name="role" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">Semua peran</option>
            @foreach ($roleLabels as $key => $label)
                <option value="{{ $key }}" @selected(($filters['role'] ?? null) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">Semua status</option>
            <option value="aktif" @selected(($filters['status'] ?? null) === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(($filters['status'] ?? null) === 'nonaktif')>Nonaktif</option>
        </select>
        <div class="flex gap-2">
            <x-button type="submit">Cari</x-button>
            @if (filled($filters['q'] ?? null) || filled($filters['role'] ?? null) || filled($filters['status'] ?? null))
                <x-button :href="route('admin.users.index')" variant="ghost">Reset</x-button>
            @endif
        </div>
    </form>

    @if ($users->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">Tidak ada akun yang cocok.</div>
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Akun</th>
                        <th class="px-4 py-3">Telepon</th>
                        <th class="px-4 py-3">Peran</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Dibuat</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($users as $account)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $account->name }}</span>
                                @if ($account->is(auth()->user()))<span class="ml-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">Anda</span>@endif
                                <span class="block text-xs text-gray-500">{{ $account->email }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $account->phone ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    'bg-purple-100 text-purple-800' => $account->role === 'admin',
                                    'bg-sky-100 text-sky-800' => $account->role === 'staff',
                                    'bg-gray-100 text-gray-700' => $account->role === 'user',
                                ])>{{ $roleLabels[$account->role] ?? $account->role }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $account->is_active,
                                    'bg-red-100 text-red-800' => ! $account->is_active,
                                ])>{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $account->created_at->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('admin.users.edit', $account) }}" class="font-medium text-emerald-700 hover:underline">Edit</a>
                                @unless ($account->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.users.destroy', $account) }}" class="ml-3 inline"
                                          onsubmit="return confirm('Hapus akun {{ addslashes($account->email) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    @endif

</x-layouts.dashboard>
