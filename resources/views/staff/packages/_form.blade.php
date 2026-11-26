{{-- Form bersama tambah & edit paket. Variabel: $package, $itemsByCategory, $selected, $action, $method, $submit --}}
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Informasi paket</h2>
        <div class="mt-4 space-y-4">
            <x-input label="Nama paket" name="name" :value="$package->name" required />

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi <span class="font-normal text-gray-500">(opsional)</span></label>
                <textarea id="description" name="description" rows="3" maxlength="1000"
                          class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('description', $package->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input label="Diskon paket (%)" name="discount_percent" type="number" min="0" max="50" :value="$package->discount_percent" required />
                    <p class="mt-1 text-xs text-gray-500">0 sampai 50. Dipotongkan dari total tarif semua alat dalam paket.</p>
                </div>
                <label class="flex items-center gap-2 self-end pb-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $package->is_active))
                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    Aktif (tampil di katalog dan dapat dipesan)
                </label>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-semibold">Isi paket</h2>
        <p class="text-xs text-gray-500">Isi jumlah unit tiap alat yang termasuk dalam paket. Biarkan 0 untuk alat yang tidak termasuk.</p>

        @error('items') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        @error('items.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

        @foreach ($itemsByCategory as $categoryName => $categoryItems)
            <h3 class="mt-5 text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $categoryName }}</h3>
            <div class="divide-y divide-gray-100">
                @foreach ($categoryItems as $item)
                    <div class="flex items-center justify-between gap-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate font-medium">
                                {{ $item->name }}
                                @unless ($item->is_active)<span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">nonaktif</span>@endunless
                            </p>
                            <p class="text-xs text-gray-500">{{ \App\Support\Format::rupiah($item->daily_rate) }} / hari</p>
                        </div>
                        <input type="number" name="items[{{ $item->id }}]" min="0" max="20"
                               value="{{ old('items.' . $item->id, $selected[$item->id] ?? 0) }}" aria-label="Jumlah {{ $item->name }}"
                               class="w-20 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                @endforeach
            </div>
        @endforeach
    </section>

    <div class="flex gap-3">
        <x-button type="submit">{{ $submit }}</x-button>
        <x-button :href="route('staff.packages.index')" variant="ghost">Batal</x-button>
    </div>
</form>
