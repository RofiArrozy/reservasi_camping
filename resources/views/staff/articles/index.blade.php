<x-layouts.dashboard title="Artikel" heading="Artikel Blog">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Tulis dan kelola artikel blog. Hanya artikel berstatus terbit yang tampil di publik.</p>
        <x-button :href="route('staff.articles.create')">+ Tulis Artikel</x-button>
    </div>

    <form method="GET" action="{{ route('staff.articles.index') }}"
          class="mt-4 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_11rem_auto]">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul..."
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">Semua status</option>
            <option value="published" @selected(($filters['status'] ?? null) === 'published')>Terbit</option>
            <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>Draf</option>
        </select>
        <div class="flex gap-2">
            <x-button type="submit">Cari</x-button>
            @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null))
                <x-button :href="route('staff.articles.index')" variant="ghost">Reset</x-button>
            @endif
        </div>
    </form>

    @if ($articles->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">Tidak ada artikel.</div>
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Penulis</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Tanggal terbit</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($articles as $article)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $article->title }}</td>
                            <td class="px-4 py-3">{{ $article->author?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $article->status === 'published',
                                    'bg-amber-100 text-amber-800' => $article->status === 'draft',
                                ])>{{ $article->status === 'published' ? 'Terbit' : 'Draf' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $article->published_at?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @if ($article->status === 'published')
                                    <a href="{{ route('blog.show', $article) }}" target="_blank" class="text-gray-500 hover:underline">Lihat</a>
                                @endif
                                <a href="{{ route('staff.articles.edit', $article) }}" class="ml-3 font-medium text-emerald-700 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('staff.articles.destroy', $article) }}" class="ml-3 inline"
                                      onsubmit="return confirm('Hapus artikel {{ addslashes($article->title) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $articles->links() }}</div>
    @endif

</x-layouts.dashboard>
