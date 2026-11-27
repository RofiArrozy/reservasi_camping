<x-layouts.dashboard title="Tambah Akun" heading="Tambah Akun">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar akun</a>
    <div class="mt-4">
        @include('admin.users._form', ['action' => route('admin.users.store'), 'method' => 'POST', 'submit' => 'Buat Akun'])
    </div>
</x-layouts.dashboard>
