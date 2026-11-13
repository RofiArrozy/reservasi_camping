<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Package;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Eager loading 'items' agar harga paket (dihitung dari alatnya) tidak memicu N+1 query.
        $packages = Package::active()->with('items')->orderBy('id')->take(3)->get();

        // Alat terpopuler = paling sering muncul di rincian booking.
        $popularItems = Item::active()
            ->with('category')
            ->withCount('bookingItems')
            ->orderByDesc('booking_items_count')
            ->orderBy('name')
            ->take(6)
            ->get();

        return view('home', compact('packages', 'popularItems'));
    }
}
