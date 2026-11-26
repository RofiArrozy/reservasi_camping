<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ItemRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Item;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * CRUD alat camping (Staff & Admin): kategori, stok total, tarif harian, nilai penggantian.
 */
class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q'        => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'status'   => ['nullable', 'in:aktif,nonaktif'],
        ]);

        $items = Item::with('category')
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('name', 'like', '%' . $v . '%'))
            ->when($filters['kategori'] ?? null, fn ($q, $slug) =>
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when(($filters['status'] ?? null) === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('staff.items.index', [
            'items'      => $items,
            'categories' => Category::orderBy('name')->get(),
            'filters'    => $filters,
        ]);
    }

    public function create(): View
    {
        return view('staff.items.create', [
            'item'       => new Item(['total_stock' => 1, 'is_active' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $item = new Item(Arr::only($data, ['category_id', 'name', 'description', 'total_stock', 'daily_rate', 'replacement_cost']));
        $item->slug      = $this->uniqueSlug($data['name']);
        $item->is_active = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $item->image_path = $request->file('image')->store('items', 'public');
        }

        $item->save();

        ActivityLog::record('item.created', $item, "Menambah alat: {$item->name}", [
            'stock' => $item->total_stock, 'rate' => $item->daily_rate,
        ]);

        return redirect()->route('staff.items.index')->with('success', "Alat \"{$item->name}\" berhasil ditambahkan.");
    }

    public function edit(Item $item, InventoryService $inventory): View
    {
        return view('staff.items.edit', [
            'item'       => $item,
            'categories' => Category::orderBy('name')->get(),
            'peak'       => $inventory->peakReservation($item),
        ]);
    }

    public function update(ItemRequest $request, Item $item): RedirectResponse
    {
        $data = $request->validated();

        // Slug sengaja tidak diubah agar tautan lama tetap berlaku.
        $item->fill(Arr::only($data, ['category_id', 'name', 'description', 'total_stock', 'daily_rate', 'replacement_cost']));
        $item->is_active = $request->boolean('is_active');

        $oldImage = $item->image_path;

        if ($request->hasFile('image')) {
            $item->image_path = $request->file('image')->store('items', 'public');
        } elseif ($request->boolean('remove_image')) {
            $item->image_path = null;
        }

        // Catat perubahan (nilai lama -> baru) sebelum disimpan.
        $changes = Arr::except($item->getDirty(), ['image_path', 'updated_at']);
        $before  = Arr::only($item->getOriginal(), array_keys($changes));

        $item->save();

        if ($oldImage && $oldImage !== $item->image_path) {
            Storage::disk('public')->delete($oldImage);
        }

        if ($changes) {
            ActivityLog::record('item.updated', $item, "Mengubah alat: {$item->name}", ['old' => $before, 'new' => $changes]);
        }

        return redirect()->route('staff.items.index')->with('success', "Alat \"{$item->name}\" berhasil diperbarui.");
    }

    public function destroy(Item $item, InventoryService $inventory): RedirectResponse
    {
        if ($count = $inventory->activeBookingCount($item)) {
            return back()->with('error', "Alat \"{$item->name}\" tidak dapat dihapus karena masih ada di {$count} booking aktif/mendatang. Nonaktifkan saja bila tidak ingin disewakan lagi.");
        }

        if ($packages = $item->packages()->pluck('name')->all()) {
            return back()->with('error', "Alat \"{$item->name}\" masih menjadi bagian dari paket: " . implode(', ', $packages) . '. Keluarkan dari paket terlebih dahulu.');
        }

        // Soft delete: riwayat booking lama tetap utuh.
        $item->delete();

        ActivityLog::record('item.deleted', $item, "Menghapus alat: {$item->name}");

        return redirect()->route('staff.items.index')->with('success', "Alat \"{$item->name}\" dihapus.");
    }

    /** Slug unik (termasuk yang sudah dihapus lunak), mis. tenda-dome, tenda-dome-2. */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'alat';
        $slug = $base;

        for ($i = 2; Item::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
