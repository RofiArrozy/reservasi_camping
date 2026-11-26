{{-- Form bersama untuk tambah & edit alat. Variabel: $item, $categories, $action, $method, $peak, $submit --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Informasi alat</h2>

        <div class="mt-4 space-y-4">
            <x-input label="Nama alat" name="name" :value="$item->name" required />

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" required
                        class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-1 {{ $errors->has('category_id') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500' }}">
                    <option value="">Pilih kategori...</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi <span class="font-normal text-gray-500">(opsional)</span></label>
                <textarea id="description" name="description" rows="3" maxlength="1000"
                          class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('description', $item->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Stok & harga</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <div>
                <x-input label="Total stok (unit)" name="total_stock" type="number" min="0" max="9999" :value="$item->total_stock" required />
                @if (! empty($peak))
                    <p class="mt-1 text-xs text-amber-700">Minimal {{ $peak }} unit: sudah dipesan pada booking aktif/mendatang.</p>
                @endif
            </div>
            <x-input label="Tarif harian (Rp)" name="daily_rate" type="number" min="0" :value="$item->daily_rate" required />
            <div>
                <x-input label="Nilai penggantian (Rp)" name="replacement_cost" type="number" min="0" :value="$item->replacement_cost" required />
                <p class="mt-1 text-xs text-gray-500">Dipakai untuk tagihan jika alat rusak/hilang.</p>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Foto & status</h2>

        <div class="mt-4 space-y-4">
            @if ($item->exists && $item->image_path)
                <div class="flex items-center gap-4">
                    <x-item-thumb :item="$item" class="h-20 w-20 rounded-lg border border-gray-200" />
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        Hapus foto saat ini
                    </label>
                </div>
            @endif

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">Foto <span class="font-normal text-gray-500">(opsional, JPG/PNG/WebP, maks. 2 MB)</span></label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100">
                @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))
                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                Aktif (tampil di katalog dan dapat dipesan)
            </label>
        </div>
    </section>

    <div class="flex gap-3">
        <x-button type="submit">{{ $submit }}</x-button>
        <x-button :href="route('staff.items.index')" variant="ghost">Batal</x-button>
    </div>
</form>
