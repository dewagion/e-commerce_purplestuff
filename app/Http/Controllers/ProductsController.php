<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $selected = $request->query('category', 'all');
        $artistSlug = $request->query('artist', 'all');
        $availability = $request->query('availability', 'all');
        $productType = $request->query('type', 'all');
        $priceRange = $request->query('price', 'all');
        $sort = $request->query('sort', 'recent');

        $idsParam = trim((string) $request->query('ids', ''));
        $ids = $idsParam !== '' ? array_values(array_filter(array_map('trim', explode(',', $idsParam)))) : [];

        $categories = Category::orderBy('name')->get()->map(function (Category $category) use ($selected) {
            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'active' => $selected === $category->slug,
            ];
        })->values()->all();

        $artists = Artist::orderBy('name')->get()->map(function (Artist $artist) use ($artistSlug) {
            return [
                'name' => $artist->name,
                'slug' => $artist->slug,
                'active' => $artistSlug === $artist->slug,
            ];
        })->values()->all();

        $selectedCategoryName = collect($categories)->firstWhere('slug', $selected)['name'] ?? null;
        $selectedArtistName = collect($artists)->firstWhere('slug', $artistSlug)['name'] ?? null;

        $query = Product::with(['artist','category','images','variants','sizes','optionStocks'])
            ->orderByRaw("CASE WHEN product_status = 'upcoming' THEN 2 WHEN stock > 0 THEN 0 ELSE 1 END");

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if ($artistSlug !== 'all') {
            $query->whereHas('artist', fn ($q2) => $q2->where('slug', $artistSlug));
        }

        if ($selected !== 'all') {
            $query->whereHas('category', fn ($q2) => $q2->where('slug', $selected));
        }

        if ($productType !== 'all') {
            $query->where('type', $productType);
        }

        if ($availability === 'in_stock') {
            $query->where('in_stock', true)->where('stock', '>', 0)->where('product_status', '!=', 'upcoming');
        } elseif ($availability === 'sold_out') {
            $query->where(function ($stockQuery) {
                $stockQuery->where('in_stock', false)->orWhere('stock', '<=', 0);
            })->where('product_status', '!=', 'upcoming');
        } elseif ($availability === 'upcoming') {
            $query->where('product_status', 'upcoming');
        }

        if ($priceRange === 'under_500') {
            $query->where('price', '<', 500000);
        } elseif ($priceRange === '500_1000') {
            $query->whereBetween('price', [500000, 1000000]);
        } elseif ($priceRange === '1000_2000') {
            $query->whereBetween('price', [1000001, 2000000]);
        } elseif ($priceRange === 'above_2000') {
            $query->where('price', '>', 2000000);
        }

        if ($q !== '') {
            $qLower = mb_strtolower($q);
            $query->where(function ($qq) use ($qLower) {
                $qq->whereRaw('LOWER(name) LIKE ?', ["%{$qLower}%"])
                   ->orWhereRaw('LOWER(`desc`) LIKE ?', ["%{$qLower}%"])
                   ->orWhereHas('category', fn ($c) => $c->whereRaw('LOWER(name) LIKE ?', ["%{$qLower}%"]))
                   ->orWhereHas('artist', fn ($a) => $a->whereRaw('LOWER(name) LIKE ?', ["%{$qLower}%"]));
            });
        }

        match ($sort) {
            'price_low' => $query->orderBy('price')->orderByDesc('created_at'),
            'price_high' => $query->orderByDesc('price')->orderByDesc('created_at'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('created_at'),
        };

        $wishlistIds = $request->user()?->wishlistProducts()->pluck('products.id')->all() ?? [];

        $products = $query->get()
            ->map(function ($p) use ($wishlistIds) {
                $data = $p->toCatalogArray();
                $data['wishlisted'] = in_array($p->id, $wishlistIds, true);
                return $data;
            })
            ->toArray();

        $pageTitle = 'All Products';
        if (!empty($ids)) {
            $pageTitle = 'Koleksi Pilihan';
        } elseif ($selectedCategoryName) {
            $pageTitle = $selectedCategoryName;
        } elseif ($selectedArtistName) {
            $pageTitle = $selectedArtistName;
        } elseif ($q !== '') {
            $pageTitle = 'Hasil Pencarian';
        }

        return view('products.index', [
            'products' => $products,
            'q' => $q,
            'selected' => $selected,
            'artistSlug' => $artistSlug,
            'ids' => $idsParam,
            'categories' => $categories,
            'artists' => $artists,
            'availability' => $availability,
            'productType' => $productType,
            'priceRange' => $priceRange,
            'sort' => $sort,
            'pageTitle' => $pageTitle,
            'selectedCategoryName' => $selectedCategoryName,
            'selectedArtistName' => $selectedArtistName,
            'resultCount' => count($products),
        ]);
    }
}
