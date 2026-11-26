<x-layouts.dashboard :title="'Edit ' . $package->name" heading="Edit Paket">
    <a href="{{ route('staff.packages.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar paket</a>
    <div class="mt-4">
        @include('staff.packages._form', ['action' => route('staff.packages.update', $package), 'method' => 'PUT', 'submit' => 'Simpan Perubahan'])
    </div>
</x-layouts.dashboard>
