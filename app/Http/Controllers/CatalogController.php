<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /** Katalog: daftar paket + daftar alat (dengan pencarian & filter kategori). */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q'        => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:100'],
        ]);

        $items = Item::active()
            ->with('category')
            ->when($filters['kategori'] ?? null, fn ($query, $slug) =>
                $query->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->when($filters['q'] ?? null, fn ($query, $keyword) =>
                $query->where('name', 'like', '%' . $keyword . '%'))
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        $hasFilter = filled($filters['q'] ?? null) || filled($filters['kategori'] ?? null);

        return view('catalog.index', [
            'items'      => $items,
            'categories' => Category::orderBy('name')->get(),
            // Paket disembunyikan saat pengguna sedang mencari/memfilter alat.
            'packages'   => $hasFilter ? collect() : Package::active()->with('items')->orderBy('name')->get(),
            'filters'    => $filters,
        ]);
    }

    public function showItem(Item $item): View
    {
        abort_unless($item->is_active, 404);

        $item->load('category');

        $related = Item::active()
            ->with('category')
            ->where('category_id', $item->category_id)
            ->whereKeyNot($item->id)
            ->orderBy('name')
            ->take(4)
            ->get();

        return view('catalog.item', compact('item', 'related'));
    }

    public function showPackage(Package $package): View
    {
        abort_unless($package->is_active, 404);

        $package->load('items.category');

        return view('catalog.package', compact('package'));
    }
}
