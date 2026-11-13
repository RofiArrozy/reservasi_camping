<x-layouts.app :title="$article->title">
    <article class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">

        <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('blog.index') }}" class="hover:text-emerald-700">Blog</a>
            <span class="mx-1">/</span>
            <span class="text-gray-800">{{ \Illuminate\Support\Str::limit($article->title, 40) }}</span>
        </nav>

        <h1 class="mt-4 text-3xl font-bold leading-tight sm:text-4xl">{{ $article->title }}</h1>

        @php
            // Perkiraan waktu baca: ±200 kata per menit
            $readingMinutes = max(1, (int) ceil(str_word_count(strip_tags($article->content)) / 200));
        @endphp
        <p class="mt-3 text-sm text-gray-500">
            {{ $article->published_at->translatedFormat('d F Y') }}
            @if ($article->author) &middot; oleh {{ $article->author->name }} @endif
            &middot; {{ $readingMinutes }} menit baca
        </p>

        @if ($article->thumbnail_path)
            <img src="{{ asset('storage/' . $article->thumbnail_path) }}" alt="{{ $article->title }}"
                 class="mt-6 w-full rounded-xl object-cover">
        @endif

        {{-- Isi artikel: dipecah per paragraf dan di-escape (aman dari XSS) --}}
        <div class="mt-8 text-gray-700">
            @foreach (preg_split('/\R{2,}/', trim($article->content)) as $paragraph)
                <p class="mb-4 leading-relaxed">{!! nl2br(e($paragraph)) !!}</p>
            @endforeach
        </div>

        @if ($others->isNotEmpty())
            <hr class="my-10 border-gray-200">
            <h2 class="text-xl font-bold">Artikel Lainnya</h2>
            <div class="mt-4 grid gap-6 sm:grid-cols-2">
                @foreach ($others->take(2) as $other)
                    <x-article-card :article="$other" />
                @endforeach
            </div>
        @endif

    </article>
</x-layouts.app>
