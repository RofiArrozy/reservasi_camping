{{-- Form bersama tulis & edit artikel. Variabel: $article, $action, $method, $submit --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="space-y-4">
            <x-input label="Judul" name="title" :value="$article->title" required />

            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700">Ringkasan <span class="font-normal text-gray-500">(opsional, tampil di kartu blog)</span></label>
                <textarea id="excerpt" name="excerpt" rows="2" maxlength="500"
                          class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('excerpt', $article->excerpt) }}</textarea>
                @error('excerpt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">Isi artikel <span class="text-red-500">*</span></label>
                <textarea id="content" name="content" rows="14" required
                          class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm leading-relaxed focus:outline-none focus:ring-1 {{ $errors->has('content') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500' }}">{{ old('content', $article->content) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Pisahkan paragraf dengan satu baris kosong. Tag HTML tidak diproses (ditampilkan sebagai teks biasa).</p>
                @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="space-y-4">
            @if ($article->exists && $article->thumbnail_path)
                <div class="flex items-center gap-4">
                    <img src="{{ asset('storage/' . $article->thumbnail_path) }}" alt="" class="h-20 w-32 rounded-lg border border-gray-200 object-cover">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remove_thumbnail" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        Hapus gambar saat ini
                    </label>
                </div>
            @endif

            <div>
                <label for="thumbnail" class="block text-sm font-medium text-gray-700">Gambar sampul <span class="font-normal text-gray-500">(opsional, JPG/PNG/WebP, maks. 2 MB)</span></label>
                <input id="thumbnail" name="thumbnail" type="file" accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100">
                @error('thumbnail') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:w-60">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="draft" @selected(old('status', $article->status) === 'draft')>Draf (tidak tampil di publik)</option>
                    <option value="published" @selected(old('status', $article->status) === 'published')>Terbit</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex gap-3">
        <x-button type="submit">{{ $submit }}</x-button>
        <x-button :href="route('staff.articles.index')" variant="ghost">Batal</x-button>
    </div>
</form>
