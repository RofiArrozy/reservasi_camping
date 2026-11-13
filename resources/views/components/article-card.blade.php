@props(['article'])

<a href="{{ route('blog.show', $article) }}"
   class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">

    @if ($article->thumbnail_path)
        <img src="{{ asset('storage/' . $article->thumbnail_path) }}" alt="{{ $article->title }}"
             class="h-44 w-full object-cover" loading="lazy">
    @else
        <div class="flex h-44 items-center justify-center bg-emerald-50 text-5xl" aria-hidden="true">🏕️</div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs text-gray-500">{{ $article->published_at?->translatedFormat('d F Y') }}</p>
        <h3 class="mt-1 text-lg font-semibold group-hover:text-emerald-700">{{ $article->title }}</h3>
        <p class="mt-2 text-sm text-gray-600">
            {{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->content), 140) }}
        </p>
        <span class="mt-auto pt-4 text-sm font-medium text-emerald-700">Baca selengkapnya &rarr;</span>
    </div>
</a>
