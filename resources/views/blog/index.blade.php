<x-layouts.app title="Blog">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        <h1 class="text-2xl font-bold">Blog</h1>
        <p class="mt-1 text-gray-600">Tips, panduan, dan informasi seputar camping.</p>

        @if ($articles->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                Belum ada artikel yang diterbitkan.
            </div>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>

            <div class="mt-8">{{ $articles->links() }}</div>
        @endif

    </div>
</x-layouts.app>
