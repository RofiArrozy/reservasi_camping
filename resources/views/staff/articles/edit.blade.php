<x-layouts.dashboard :title="'Edit ' . $article->title" heading="Edit Artikel">
    <a href="{{ route('staff.articles.index') }}" class="text-sm text-gray-500 hover:text-emerald-700">&larr; Kembali ke daftar artikel</a>
    <div class="mt-4">
        @include('staff.articles._form', ['action' => route('staff.articles.update', $article), 'method' => 'PUT', 'submit' => 'Simpan Perubahan'])
    </div>
</x-layouts.dashboard>
