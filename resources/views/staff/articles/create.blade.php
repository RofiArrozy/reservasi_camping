<x-layouts.dashboard title="Tulis Artikel" heading="Tulis Artikel">
    <a href="{{ route('staff.articles.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar artikel</a>
    <div class="mt-4">
        @include('staff.articles._form', ['action' => route('staff.articles.store'), 'method' => 'POST', 'submit' => 'Simpan Artikel'])
    </div>
</x-layouts.dashboard>
