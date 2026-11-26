<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kelola kategori alat (satu halaman: tambah, ubah, hapus).
 */
class CategoryController extends Controller
{
    public function index(): View
    {
        return view('staff.categories.index', [
            'categories' => Category::withCount('items')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $category = Category::create($data + ['slug' => $this->uniqueSlug($data['name'])]);

        ActivityLog::record('category.created', $category, "Menambah kategori: {$category->name}");

        return back()->with('success', "Kategori \"{$category->name}\" ditambahkan.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        ActivityLog::record('category.updated', $category, "Mengubah kategori: {$category->name}");

        return back()->with('success', "Kategori \"{$category->name}\" diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Termasuk alat yang sudah dihapus lunak, karena relasi database melindunginya.
        if ($category->items()->withTrashed()->exists()) {
            return back()->with('error', "Kategori \"{$category->name}\" tidak dapat dihapus karena masih memiliki alat.");
        }

        $category->delete();

        ActivityLog::record('category.deleted', $category, "Menghapus kategori: {$category->name}");

        return back()->with('success', "Kategori \"{$category->name}\" dihapus.");
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:300'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
            'name.unique'   => 'Nama kategori sudah dipakai.',
            'description.max' => 'Deskripsi maksimal 300 karakter.',
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;

        for ($i = 2; Category::where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }
}
