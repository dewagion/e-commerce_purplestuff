<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartPersistenceService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addRedirect()
    {
        return redirect()
            ->route('cart.index')
            ->with('error', 'Halaman tambah ke keranjang tidak bisa dibuka langsung. Silakan kembali ke produk lalu coba lagi.');
    }

    public function index(CartPersistenceService $cartService)
    {
        $cart = $cartService->currentCart();
        $total = 0;

        foreach ($cart as $item) {
            $total += ((int) $item['price'] * (int) $item['qty']);
        }

        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request, CartPersistenceService $cartService)
    {
        /**
         * PERBAIKAN:
         * 1. Keranjang user login disimpan ke database, jadi tidak hilang setelah logout/login.
         * 2. Data name, price, image, sku, in_stock, dan shipping dari form tetap tidak dipercaya.
         *    Harga dan data produk asli selalu diambil dari database.
         */
        $data = $request->validate([
            'id'      => ['required', 'string', 'exists:products,id'],
            'type'    => ['nullable', 'string'],
            'size'    => ['nullable', 'string', 'max:100'],
            'qty'     => ['nullable', 'integer', 'min:1', 'max:99'],
            'variant' => ['nullable', 'string', 'max:150'],
        ]);

        $product = Product::with(['images', 'variants', 'sizes', 'optionStocks'])->findOrFail($data['id']);

        if (method_exists($product, 'isUpcoming') && $product->isUpcoming()) {
            return back()->with('error', 'Produk ini masih Upcoming dan belum bisa ditambahkan ke keranjang.');
        }

        $type = $product->type ?: 'accessory';
        $size = $cartService->cleanNullableString($data['size'] ?? null);
        $variant = $cartService->cleanNullableString($data['variant'] ?? null);

        $validSizes = $product->sizes->pluck('size')->filter()->values()->all();
        $validVariants = $product->variants->pluck('name')->filter()->values()->all();

        if ($type === 'apparel' && !$size) {
            return back()->with('error', 'Silakan pilih ukuran terlebih dahulu.');
        }

        if ($size && count($validSizes) > 0 && !in_array($size, $validSizes, true)) {
            return back()->with('error', 'Ukuran yang dipilih tidak tersedia untuk produk ini.');
        }

        if (!$variant && count($validVariants) > 0) {
            // Menjaga kompatibilitas dengan fitur quick add lama.
            $variant = $validVariants[0];
        }

        if ($variant && count($validVariants) > 0 && !in_array($variant, $validVariants, true)) {
            return back()->with('error', 'Pilihan varian tidak tersedia untuk produk ini.');
        }

        $availableStock = $cartService->availableStock($product, $variant, $size);

        if ($availableStock <= 0) {
            return back()->with('error', 'Stok untuk pilihan produk ini sedang habis dan tidak bisa ditambahkan ke keranjang.');
        }

        $qty = max(1, min($availableStock, (int) ($data['qty'] ?? 1)));

        $key = $product->id . ':' . ($size ?: '-') . ':' . ($variant ?: '-');

        $cart = $cartService->currentCart();

        $requestedTotal = $qty;

        if (isset($cart[$key])) {
            $requestedTotal = (int) $cart[$key]['qty'] + $qty;
            $cart[$key]['qty'] = min($availableStock, $requestedTotal);
        } else {
            $cart[$key] = $cartService->makeCartItem($product, $key, $qty, $size, $variant);
        }

        $cartService->putCart($cart);

        if ($requestedTotal > $availableStock) {
            return back()->with('success', 'Jumlah produk disesuaikan dengan stok tersedia: ' . $availableStock . ' item.');
        }

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function remove(Request $request, CartPersistenceService $cartService)
    {
        $request->validate([
            'key' => ['required', 'string'],
        ]);

        $cart = $cartService->currentCart();
        unset($cart[$request->key]);
        $cartService->putCart($cart);

        return back()
            ->with('success', 'Produk dihapus dari keranjang.')
            ->with('cart_open', $request->boolean('keep_open'));
    }

    public function clear(Request $request, CartPersistenceService $cartService)
    {
        $cartService->clearCurrentCart();

        return back()
            ->with('success', 'Keranjang dikosongkan.')
            ->with('cart_open', $request->boolean('keep_open'));
    }

    public function qty(Request $request, CartPersistenceService $cartService)
    {
        $data = $request->validate([
            'key'   => ['required', 'string'],
            'delta' => ['required', 'integer'],
        ]);

        $cart = $cartService->currentCart();

        if (!isset($cart[$data['key']])) {
            $cartService->putCart($cart);

            return back()->with('cart_open', $request->boolean('keep_open'));
        }

        $current = (int) ($cart[$data['key']]['qty'] ?? 1);
        $next = $current + (int) $data['delta'];
        $maxQty = max(1, (int) ($cart[$data['key']]['available_stock'] ?? $cart[$data['key']]['stock'] ?? 1));

        $cart[$data['key']]['qty'] = max(1, min($maxQty, $next));
        $cartService->putCart($cart);

        if ((int) $data['delta'] > 0 && $current >= $maxQty) {
            return back()
                ->with('cart_open', $request->boolean('keep_open'))
                ->with('error', 'Jumlah produk tidak bisa melebihi stok tersedia: ' . $maxQty . ' item.');
        }

        return back()->with('cart_open', $request->boolean('keep_open'));
    }
}
