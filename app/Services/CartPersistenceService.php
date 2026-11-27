<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CartPersistenceService
{
    public const SESSION_KEY = 'cart';
    public const LOADED_KEY = 'cart_loaded_from_database';

    public function ensureLoadedForCurrentUser(): void
    {
        if (!Auth::guard('web')->check()) {
            return;
        }

        if (session()->has(self::LOADED_KEY)) {
            return;
        }

        try {
            $cart = $this->mergeSessionCartIntoUserCart(
                Auth::guard('web')->user(),
                $this->sessionCart()
            );

            session([
                self::SESSION_KEY => $cart,
                self::LOADED_KEY => true,
            ]);
        } catch (Throwable $exception) {
            // Jangan sampai masalah tabel/migrasi cart membuat halaman utama error.
        }
    }

    public function currentCart(): array
    {
        $this->ensureLoadedForCurrentUser();

        $cart = $this->syncCartWithProducts($this->sessionCart());
        session([self::SESSION_KEY => $cart]);

        return $cart;
    }

    public function putCart(array $cart): array
    {
        $cart = $this->syncCartWithProducts($cart);

        session([
            self::SESSION_KEY => $cart,
            self::LOADED_KEY => Auth::guard('web')->check(),
        ]);

        if (Auth::guard('web')->check()) {
            $this->storeCartForUser(Auth::guard('web')->user(), $cart);
        }

        return $cart;
    }

    public function clearCurrentCart(): void
    {
        if (Auth::guard('web')->check()) {
            $this->deleteCartForUser(Auth::guard('web')->user());
        }

        session()->forget([
            self::SESSION_KEY,
            'checkout_selected_keys',
            'checkout_shipping_options',
        ]);

        if (Auth::guard('web')->check()) {
            session([self::LOADED_KEY => true]);
        } else {
            session()->forget(self::LOADED_KEY);
        }
    }

    public function mergeSessionCartIntoUserCart(User $user, array $sessionCart): array
    {
        $databaseCart = $this->loadCartForUser($user);
        $sessionCart = $this->syncCartWithProducts($sessionCart);

        $merged = $databaseCart;

        foreach ($sessionCart as $key => $item) {
            if (isset($merged[$key])) {
                $merged[$key]['qty'] = (int) $merged[$key]['qty'] + (int) ($item['qty'] ?? 1);
                continue;
            }

            $merged[$key] = $item;
        }

        return $this->storeCartForUser($user, $merged);
    }

    public function loadCartForUser(User $user): array
    {
        if (!$this->cartTableReady()) {
            return [];
        }

        $items = CartItem::query()
            ->with(['product.images', 'product.variants', 'product.sizes', 'product.optionStocks'])
            ->where('user_id', $user->id)
            ->oldest()
            ->get();

        $cart = [];

        foreach ($items as $item) {
            $product = $item->product;

            if (!$product) {
                continue;
            }

            $size = $this->cleanNullableString($item->size);
            $variant = $this->cleanNullableString($item->variant);
            $key = $product->id . ':' . ($size ?: '-') . ':' . ($variant ?: '-');

            $cart[$key] = [
                'key' => $key,
                'id' => $product->id,
                'qty' => max(1, (int) $item->qty),
                'size' => $size,
                'variant' => $variant,
            ];
        }

        return $this->syncCartWithProducts($cart);
    }

    public function storeCartForUser(User $user, array $cart): array
    {
        $cart = $this->syncCartWithProducts($cart);

        if (!$this->cartTableReady()) {
            return $cart;
        }

        DB::transaction(function () use ($user, $cart) {
            CartItem::query()
                ->where('user_id', $user->id)
                ->delete();

            foreach ($cart as $key => $item) {
                CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $item['id'],
                    'key' => $key,
                    'size' => $this->cleanNullableString($item['size'] ?? null),
                    'variant' => $this->cleanNullableString($item['variant'] ?? null),
                    'qty' => max(1, min((int) ($item['available_stock'] ?? $item['stock'] ?? 1), (int) ($item['qty'] ?? 1))),
                ]);
            }
        });

        return $cart;
    }

    public function deleteCartForUser(User $user): void
    {
        if (!$this->cartTableReady()) {
            return;
        }

        CartItem::query()
            ->where('user_id', $user->id)
            ->delete();
    }

    public function syncCartWithProducts(array $cart): array
    {
        if (empty($cart)) {
            return [];
        }

        $productIds = collect($cart)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($productIds)) {
            return [];
        }

        $products = Product::with(['images', 'variants', 'sizes', 'optionStocks'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $synced = [];

        foreach ($cart as $item) {
            $productId = $item['id'] ?? null;
            $product = $productId ? $products->get($productId) : null;

            if (!$product) {
                continue;
            }

            $size = $this->cleanNullableString($item['size'] ?? null);
            $variant = $this->cleanNullableString($item['variant'] ?? null);
            $availableStock = $this->availableStock($product, $variant, $size);

            if ($availableStock <= 0) {
                continue;
            }

            $qty = max(1, min($availableStock, (int) ($item['qty'] ?? 1)));

            $validSizes = $product->sizes->pluck('size')->filter()->values()->all();
            $validVariants = $product->variants->pluck('name')->filter()->values()->all();

            if ($product->type === 'apparel' && !$size) {
                continue;
            }

            if ($size && count($validSizes) > 0 && !in_array($size, $validSizes, true)) {
                continue;
            }

            if ($variant && count($validVariants) > 0 && !in_array($variant, $validVariants, true)) {
                continue;
            }

            $key = $product->id . ':' . ($size ?: '-') . ':' . ($variant ?: '-');
            $synced[$key] = $this->makeCartItem($product, $key, $qty, $size, $variant);
        }

        return $synced;
    }

    public function makeCartItem(Product $product, string $key, int $qty, ?string $size, ?string $variant): array
    {
        $availableStock = $this->availableStock($product, $variant, $size);

        $price = method_exists($product, 'sellingPriceFor')
            ? $product->sellingPriceFor($variant, $size)
            : (method_exists($product, 'sellingPrice') ? $product->sellingPrice() : (int) $product->price);

        $image = method_exists($product, 'imageForOption')
            ? $product->imageForOption($variant, $size)
            : $product->primary_image;

        return [
            'key' => $key,
            'id' => $product->id,
            'name' => $product->name,
            'price' => $price,
            'image' => $image ?: ($product->images->first()?->path ?: asset('images/placeholder.jpg')),
            'qty' => max(1, min($availableStock, $qty)),
            'type' => $product->type ?: 'accessory',
            'size' => $size,
            'variant' => $variant,
            'sku' => $product->sku,
            'in_stock' => $availableStock > 0,
            'stock' => (int) ($product->stock ?? 0),
            'available_stock' => $availableStock,
            'shipping' => $product->shipping ?: '1-3 Hari Kerja',
        ];
    }

    public function isPurchasable(Product $product, ?string $variant = null, ?string $size = null): bool
    {
        return $this->availableStock($product, $variant, $size) > 0;
    }

    public function availableStock(Product $product, ?string $variant = null, ?string $size = null): int
    {
        if (method_exists($product, 'isUpcoming') && $product->isUpcoming()) {
            return 0;
        }

        return $product->availableStockFor($variant, $size);
    }

    public function cleanNullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function sessionCart(): array
    {
        $cart = session(self::SESSION_KEY, []);

        return is_array($cart) ? $cart : [];
    }

    protected function cartTableReady(): bool
    {
        try {
            return Schema::hasTable('cart_items');
        } catch (Throwable $exception) {
            return false;
        }
    }
}
