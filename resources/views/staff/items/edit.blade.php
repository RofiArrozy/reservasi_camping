<x-layouts.dashboard :title="'Edit ' . $item->name" heading="Edit Alat">
    <a href="{{ route('staff.items.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar alat</a>

    <div class="mt-4">
        @include('staff.items._form', [
            'action' => route('staff.items.update', $item),
            'method' => 'PUT',
            'submit' => 'Simpan Perubahan',
        ])
    </div>
</x-layouts.dashboard>
