<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()
            ->wishlistProducts()
            ->with(['artist', 'category', 'images', 'variants', 'sizes', 'optionStocks'])
            ->orderByDesc('wishlists.created_at')
            ->get()
            ->map(function ($product) {
                $data = $product->toCatalogArray();
                $data['wishlisted'] = true;
                return $data;
            })
            ->toArray();

        return view('wishlist.index', [
            'products' => $products,
        ]);
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();

        $alreadyWishlisted = $user->wishlistProducts()
            ->where('product_id', $product->getKey())
            ->exists();

        if ($alreadyWishlisted) {
            $user->wishlistProducts()->detach($product->getKey());
        } else {
            $user->wishlistProducts()->syncWithoutDetaching([$product->getKey()]);
        }

        return back()->with('success', $alreadyWishlisted
            ? 'Produk dihapus dari wishlist.'
            : 'Produk ditambahkan ke wishlist.');
    }
}
