<x-layouts.dashboard title="Tambah Paket" heading="Tambah Paket">
    <a href="{{ route('staff.packages.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar paket</a>
    <div class="mt-4">
        @include('staff.packages._form', ['action' => route('staff.packages.store'), 'method' => 'POST', 'submit' => 'Simpan Paket'])
    </div>
</x-layouts.dashboard>
