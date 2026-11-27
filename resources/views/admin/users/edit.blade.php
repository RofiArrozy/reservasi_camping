<x-layouts.dashboard :title="'Edit ' . $user->name" heading="Edit Akun">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar akun</a>
    <div class="mt-4">
        @include('admin.users._form', ['action' => route('admin.users.update', $user), 'method' => 'PUT', 'submit' => 'Simpan Perubahan'])
    </div>
</x-layouts.dashboard>
