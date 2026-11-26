<x-layouts.dashboard title="Kategori Alat" heading="Kategori Alat">

    <a href="{{ route('staff.items.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar alat</a>

    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Tambah --}}
    <form method="POST" action="{{ route('staff.categories.store') }}"
          class="mt-4 grid gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:grid-cols-[14rem_1fr_auto]">
        @csrf
        <input type="text" name="name" placeholder="Nama kategori baru" maxlength="100" required
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <input type="text" name="description" placeholder="Deskripsi (opsional)" maxlength="300"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <x-button type="submit">Tambah</x-button>
    </form>

    {{-- Daftar --}}
    <div class="mt-6 space-y-3">
        @forelse ($categories as $category)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <form method="POST" action="{{ route('staff.categories.update', $category) }}"
                      class="grid gap-3 sm:grid-cols-[14rem_1fr_auto] sm:items-center">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ $category->name }}" maxlength="100" required aria-label="Nama kategori"
                           class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <input type="text" name="description" value="{{ $category->description }}" maxlength="300" aria-label="Deskripsi"
                           class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <x-button type="submit" variant="secondary">Simpan</x-button>
                </form>

                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $category->items_count }} alat</span>
                    <form method="POST" action="{{ route('staff.categories.destroy', $category) }}"
                          onsubmit="return confirm('Hapus kategori {{ addslashes($category->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">Belum ada kategori.</p>
        @endforelse
    </div>

</x-layouts.dashboard>
