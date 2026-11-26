<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\PackageRequest;
use App\Models\ActivityLog;
use App\Models\BookingItem;
use App\Models\Item;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * CRUD paket sewa (bundel alat dengan diskon).
 */
class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $packages = Package::with('items')
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('name', 'like', '%' . $v . '%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('staff.packages.index', ['packages' => $packages, 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('staff.packages.create', [
            'package'     => new Package(['discount_percent' => 0, 'is_active' => true]),
            'itemsByCategory' => $this->itemsByCategory(),
            'selected'    => [],
        ]);
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $package = new Package(Arr::only($data, ['name', 'description', 'discount_percent']));
        $package->slug      = $this->uniqueSlug($data['name']);
        $package->is_active = $request->boolean('is_active');
        $package->save();

        $package->items()->sync($this->syncPayload($request->selectedItems()));

        ActivityLog::record('package.created', $package, "Menambah paket: {$package->name}", [
            'items' => $request->selectedItems(), 'discount' => $package->discount_percent,
        ]);

        return redirect()->route('staff.packages.index')->with('success', "Paket \"{$package->name}\" berhasil ditambahkan.");
    }

    public function edit(Package $package): View
    {
        $package->load('items');

        return view('staff.packages.edit', [
            'package'         => $package,
            'itemsByCategory' => $this->itemsByCategory(),
            'selected'        => $package->items->pluck('pivot.quantity', 'id')->all(),
        ]);
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        $data = $request->validated();

        // Slug sengaja tidak diubah agar tautan lama tetap berlaku.
        $package->fill(Arr::only($data, ['name', 'description', 'discount_percent']));
        $package->is_active = $request->boolean('is_active');

        $changes = Arr::except($package->getDirty(), ['updated_at']);
        $before  = Arr::only($package->getOriginal(), array_keys($changes));

        $package->save();
        $package->items()->sync($this->syncPayload($request->selectedItems()));

        ActivityLog::record('package.updated', $package, "Mengubah paket: {$package->name}", [
            'old' => $before, 'new' => $changes, 'items' => $request->selectedItems(),
        ]);

        return redirect()->route('staff.packages.index')->with('success', "Paket \"{$package->name}\" berhasil diperbarui.");
    }

    public function destroy(Package $package): RedirectResponse
    {
        // Paket yang pernah dipesan dipertahankan agar riwayat booking tetap lengkap.
        if (BookingItem::where('package_id', $package->id)->exists()) {
            return back()->with('error', "Paket \"{$package->name}\" pernah dipesan sehingga tidak dapat dihapus. Nonaktifkan saja bila tidak ingin ditawarkan lagi.");
        }

        $package->delete();

        ActivityLog::record('package.deleted', $package, "Menghapus paket: {$package->name}");

        return redirect()->route('staff.packages.index')->with('success', "Paket \"{$package->name}\" dihapus.");
    }

    /** Semua alat (dikelompokkan per kategori) untuk daftar pilihan isi paket. */
    private function itemsByCategory()
    {
        return Item::with('category')->orderBy('name')->get()->groupBy('category.name');
    }

    /** [item_id => jumlah] -> [item_id => ['quantity' => jumlah]] untuk sync(). */
    private function syncPayload(array $selected): array
    {
        return collect($selected)->map(fn ($qty) => ['quantity' => $qty])->all();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'paket';
        $slug = $base;

        for ($i = 2; Package::where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
