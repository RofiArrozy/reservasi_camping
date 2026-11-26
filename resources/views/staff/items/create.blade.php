<x-layouts.dashboard title="Tambah Alat" heading="Tambah Alat">
    <a href="{{ route('staff.items.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar alat</a>

    <div class="mt-4">
        @include('staff.items._form', [
            'action' => route('staff.items.store'),
            'method' => 'POST',
            'peak'   => null,
            'submit' => 'Simpan Alat',
        ])
    </div>
</x-layouts.dashboard>
