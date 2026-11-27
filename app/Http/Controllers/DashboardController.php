<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selected = $request->query('category', 'all');
        $artistSlug = $request->query('artist', 'all');

        $categoriesDb = Category::orderBy('name')->get()->map(function ($c) use ($selected) {
            return [
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'bg'   => $c->bg,
                'active' => ($c->slug === $selected),
            ];
        })->toArray();

        $categories = array_merge([[
            'name' => 'All Products',
            'slug' => 'all',
            'icon' => 'bag',
            'bg'   => 'bg-purple-100',
            'active' => ($selected === 'all'),
        ]], $categoriesDb);

        $artists = Artist::orderBy('name')->get()->map(function ($a) use ($artistSlug) {
            return [
                'name' => $a->name,
                'slug' => $a->slug,
                'active' => ($a->slug === $artistSlug),
            ];
        })->toArray();

        $query = Product::with(['artist','category','images','variants','sizes','optionStocks'])
            ->orderByRaw("CASE WHEN product_status = 'upcoming' THEN 2 WHEN stock > 0 THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if ($artistSlug !== 'all') {
            $query->whereHas('artist', fn ($q) => $q->where('slug', $artistSlug));
        }

        if ($selected !== 'all') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $selected));
        }

        $wishlistIds = $request->user()?->wishlistProducts()->pluck('products.id')->all() ?? [];

        $dashboardProductLimit = 6;

        // Ambil seluruh produk sesuai filter dashboard, tetapi tampilan awal tetap dibatasi 6 item.
        // Tombol View All di dashboard akan membuka sisa produk di halaman yang sama tanpa pindah route.
        $featuredProducts = $query->get()
            ->map(function ($p) use ($wishlistIds) {
                $data = $p->toCatalogArray();
                $data['wishlisted'] = in_array($p->id, $wishlistIds, true);
                return $data;
            })
            ->toArray();

        $hasMoreDashboardProducts = count($featuredProducts) > $dashboardProductLimit;

        // Dashboard baru menampilkan produk dalam kelompok per artist dengan slider horizontal.
        // Data tetap berasal dari query produk yang sama, sehingga fitur stok, harga opsi, gambar opsi, wishlist, dan modal produk tetap memakai payload Product::toCatalogArray().
        $artistProductGroups = collect($featuredProducts)
            ->groupBy(fn (array $product) => $product['artist'] ?: 'Koleksi Lainnya')
            ->map(function ($items, string $artistName) {
                $first = $items->first();

                return [
                    'name' => $artistName,
                    'slug' => $first['artist_slug'] ?? null,
                    'count' => $items->count(),
                    'products' => $items->values()->all(),
                ];
            })
            ->sortBy(fn (array $group) => $group['name'])
            ->values()
            ->all();

        $heroBanners = [];
        $categoryPromoBanners = [];
        $singlePromoBanners = [];

        if (class_exists(Banner::class) && Schema::hasTable('banners')) {
            $hasDashboardSection = Schema::hasColumn('banners', 'dashboard_section');

            $activeBanners = Banner::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get()
                ->map(function (Banner $banner) use ($hasDashboardSection) {
                    return [
                        'badge' => $banner->badge_text ?: '',
                        'title' => $banner->title,
                        'subtitle' => $banner->subtitle ?: '',
                        'image' => $banner->image,
                        'ctaText' => $banner->cta_text ?: 'Shop Now',
                        'ctaLink' => $this->resolveBannerLink($banner),
                        'dashboardSection' => $hasDashboardSection ? $banner->resolved_dashboard_section : 'hero',
                    ];
                });

            $heroBanners = $activeBanners
                ->where('dashboardSection', 'hero')
                ->values()
                ->all();

            $categoryPromoBanners = $activeBanners
                ->where('dashboardSection', 'category_grid')
                ->values()
                ->all();

            $singlePromoBanners = $activeBanners
                ->where('dashboardSection', 'single_feature')
                ->values()
                ->all();
        }

        $cartCount = collect(session('cart', []))->sum('qty');

        return view('dashboard', compact(
            'artists',
            'categories',
            'featuredProducts',
            'cartCount',
            'selected',
            'artistSlug',
            'heroBanners',
            'categoryPromoBanners',
            'singlePromoBanners',
            'dashboardProductLimit',
            'hasMoreDashboardProducts',
            'artistProductGroups'
        ));
    }

    protected function resolveBannerLink(Banner $banner): string
    {
        $targetType = $banner->resolved_target_type;
        $ids = $banner->target_product_ids_list;

        if ($targetType === 'category' && !empty($banner->target_category_slug)) {
            return route('products.index', ['category' => $banner->target_category_slug]);
        }

        if ($targetType === 'artist' && !empty($banner->target_artist_slug)) {
            return route('artist.show', ['artist' => $banner->target_artist_slug]);
        }

        if ($targetType === 'custom' && !empty($banner->cta_link)) {
            return $banner->cta_link;
        }

        // Menjaga fitur lama: banner yang punya produk pilihan tetap membuka produk pilihan.
        if (!empty($ids) && in_array($targetType, ['products', 'manual', 'all', ''], true)) {
            return route('products.index', ['ids' => implode(',', $ids)]);
        }

        if ($targetType === 'products' && !empty($ids)) {
            return route('products.index', ['ids' => implode(',', $ids)]);
        }

        if (!empty($banner->cta_link) && $banner->cta_link !== '#') {
            return $banner->cta_link;
        }

        return route('products.index');
    }
}
