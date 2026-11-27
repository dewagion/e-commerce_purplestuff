<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    protected array $iconOptions = [
        'bag',
        'star',
        'album',
        'hoodie',
        'bolt',
        'card',
        'shirt',
        'tshirt',
    ];

    public function index()
    {
        $categories = Category::orderBy('name')->paginate(12);

        return view('admin.categories.index', [
            'categories' => $categories,
            'category' => new Category(),
            'isEdit' => false,
            'iconOptions' => $this->iconOptions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['required', Rule::in($this->iconOptions)],
            'bg' => ['nullable', 'string', 'max:80'],
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name']),
            'icon' => $data['icon'],
            'bg' => $data['bg'] ?: 'from-fuchsia-500 to-violet-500',
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        $categories = Category::orderBy('name')->paginate(12);

        return view('admin.categories.index', [
            'categories' => $categories,
            'category' => $category,
            'isEdit' => true,
            'iconOptions' => $this->iconOptions,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['required', Rule::in($this->iconOptions)],
            'bg' => ['nullable', 'string', 'max:80'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($data['name'], $category->id),
            'icon' => $data['icon'],
            'bg' => $data['bg'] ?: 'from-fuchsia-500 to-violet-500',
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug;
        $counter = 1;

        while (
            Category::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}