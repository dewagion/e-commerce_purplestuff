<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    public function show(Request $request, string $artist)
    {
        $artistSlug = strtolower($artist);
        $q = trim((string) $request->query('q', ''));
        $category = $request->query('category', 'all');
        $availability = $request->query('availability', 'all');
        $productType = $request->query('type', 'all');
        $priceRange = $request->query('price', 'all');
        $sort = $request->query('sort', 'recent');

        $artistRow = Artist::where('slug', $artistSlug)->firstOrFail();
        $artistName = $artistRow->name;

        $artists = Artist::orderBy('name')->get()->map(function ($a) use ($artistSlug) {
            return [
                'name' => $a->name,
                'slug' => $a->slug,
                'active' => ($a->slug === $artistSlug),
            ];
        })->toArray();

        $categories = Category::orderBy('name')->get()->map(function ($c) use ($category) {
            return [
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'bg' => $c->bg,
                'active' => ($c->slug === $category),
            ];
        })->toArray();

        $selectedCategoryName = collect($categories)->firstWhere('slug', $category)['name'] ?? null;

        $query = Product::with(['artist', 'category', 'images', 'variants', 'sizes', 'optionStocks'])
            ->where('artist_id', $artistRow->id)
            ->orderByRaw("CASE WHEN product_status = 'upcoming' THEN 2 WHEN stock > 0 THEN 0 ELSE 1 END");

        if ($category !== 'all') {
            $query->whereHas('category', fn ($qc) => $qc->where('slug', $category));
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
                   ->orWhereHas('category', fn ($c) => $c->whereRaw('LOWER(name) LIKE ?', ["%{$qLower}%"]));
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

        $cartCount = collect(session('cart', []))->sum('qty');

        return view('artist.show', [
            'artistSlug' => $artistSlug,
            'artistName' => $artistName,
            'artistRow' => $artistRow,
            'artists' => $artists,
            'categories' => $categories,
            'products' => $products,
            'q' => $q,
            'category' => $category,
            'selectedCategoryName' => $selectedCategoryName,
            'availability' => $availability,
            'productType' => $productType,
            'priceRange' => $priceRange,
            'sort' => $sort,
            'resultCount' => count($products),
            'cartCount' => $cartCount,
        ]);
    }
}
