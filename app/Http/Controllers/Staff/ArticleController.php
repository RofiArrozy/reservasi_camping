<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ArticleRequest;
use App\Models\ActivityLog;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * CRUD artikel blog (Staff & Admin).
 */
class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q'      => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $articles = Article::with('author')
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('title', 'like', '%' . $v . '%'))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('staff.articles.index', ['articles' => $articles, 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('staff.articles.create', [
            'article' => new Article(['status' => Article::STATUS_DRAFT]),
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $article = new Article(Arr::only($data, ['title', 'excerpt', 'content', 'status']));
        $article->slug    = $this->uniqueSlug($data['title']);
        $article->user_id = $request->user()->id;

        if ($request->hasFile('thumbnail')) {
            $article->thumbnail_path = $request->file('thumbnail')->store('articles', 'public');
        }

        $article->save(); // published_at terisi otomatis oleh model jika status "published"

        ActivityLog::record('article.created', $article, "Menulis artikel: {$article->title}", ['status' => $article->status]);

        return redirect()->route('staff.articles.index')->with('success', "Artikel \"{$article->title}\" berhasil disimpan.");
    }

    public function edit(Article $article): View
    {
        return view('staff.articles.edit', compact('article'));
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $data = $request->validated();

        // Slug dan penulis asli tidak diubah.
        $article->fill(Arr::only($data, ['title', 'excerpt', 'content', 'status']));

        $oldThumb = $article->thumbnail_path;

        if ($request->hasFile('thumbnail')) {
            $article->thumbnail_path = $request->file('thumbnail')->store('articles', 'public');
        } elseif ($request->boolean('remove_thumbnail')) {
            $article->thumbnail_path = null;
        }

        $changes = Arr::only($article->getDirty(), ['title', 'status']);
        $before  = Arr::only($article->getOriginal(), array_keys($changes));

        $article->save();

        if ($oldThumb && $oldThumb !== $article->thumbnail_path) {
            Storage::disk('public')->delete($oldThumb);
        }

        ActivityLog::record('article.updated', $article, "Mengubah artikel: {$article->title}", [
            'old' => $before, 'new' => $changes,
        ]);

        return redirect()->route('staff.articles.index')->with('success', "Artikel \"{$article->title}\" berhasil diperbarui.");
    }

    public function destroy(Article $article): RedirectResponse
    {
        // Soft delete: artikel dapat dipulihkan dari database bila perlu.
        $article->delete();

        ActivityLog::record('article.deleted', $article, "Menghapus artikel: {$article->title}");

        return redirect()->route('staff.articles.index')->with('success', "Artikel \"{$article->title}\" dihapus.");
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;

        for ($i = 2; Article::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
