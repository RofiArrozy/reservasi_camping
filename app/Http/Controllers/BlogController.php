<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class BlogController extends Controller
{
    /** Daftar artikel yang sudah terbit, terbaru di atas. */
    public function index(): View
    {
        $articles = Article::published()
            ->with('author')
            ->orderByDesc('published_at')
            ->paginate(6);

        return view('blog.index', compact('articles'));
    }

    /** Baca satu artikel. Draf atau artikel terjadwal masa depan tidak dapat dibuka publik. */
    public function show(Article $article): View
    {
        abort_unless(
            $article->status === Article::STATUS_PUBLISHED
                && $article->published_at
                && $article->published_at->lte(now()),
            404
        );

        $article->load('author');

        $others = Article::published()
            ->with('author')
            ->whereKeyNot($article->id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('article', 'others'));
    }
}
