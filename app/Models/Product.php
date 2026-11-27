<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'artist_id', 'category_id', 'name', 'slug', 'sku', 'type', 'price',
        'discount_price', 'discount_active', 'product_status', 'option_pricing_active', 'option_media_active',
        'desc', 'shipping', 'in_stock', 'stock', 'primary_image', 'weight',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'price' => 'integer',
        'discount_price' => 'integer',
        'discount_active' => 'boolean',
        'option_pricing_active' => 'boolean',
        'option_media_active' => 'boolean',
        'weight' => 'integer',
        'stock' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($product->stock === null) {
                $product->stock = (bool) $product->in_stock ? 99 : 0;
            }

            $product->stock = max(0, (int) $product->stock);

            $product->product_status = in_array($product->product_status, ['ready', 'pre_order', 'upcoming'], true)
                ? $product->product_status
                : 'ready';

            $product->discount_price = $product->discount_price !== null
                ? max(0, (int) $product->discount_price)
                : null;

            if (!$product->discount_active || !$product->discount_price || $product->discount_price >= (int) $product->price) {
                $product->discount_active = false;
                $product->discount_price = null;
            }

            // Setelah fitur stok diperbaiki, stok menjadi sumber utama ketersediaan.
            // Jika total stok lebih dari 0 produk otomatis tersedia; jika 0 produk otomatis habis.
            $product->in_stock = $product->stock > 0;
        });
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('sort_order');
    }

    public function optionStocks(): HasMany
    {
        return $this->hasMany(ProductOptionStock::class, 'product_id')->orderBy('variant')->orderBy('size');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function wishedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists', 'product_id', 'user_id')->withTimestamps();
    }


    public function hasActiveDiscount(): bool
    {
        return (bool) $this->discount_active
            && $this->discount_price !== null
            && (int) $this->discount_price > 0
            && (int) $this->discount_price < (int) $this->price;
    }

    public function sellingPrice(): int
    {
        return $this->hasActiveDiscount() ? (int) $this->discount_price : (int) $this->price;
    }

    public function originalPrice(): ?int
    {
        return $this->hasActiveDiscount() ? (int) $this->price : null;
    }

    public function discountPercentage(): ?int
    {
        if (!$this->hasActiveDiscount()) {
            return null;
        }

        $price = max(1, (int) $this->price);
        $discountPrice = max(0, (int) $this->discount_price);

        return max(1, min(99, (int) round((($price - $discountPrice) / $price) * 100)));
    }

    public function usesOptionPricing(): bool
    {
        return (bool) ($this->option_pricing_active ?? false);
    }

    public function usesOptionMedia(): bool
    {
        return (bool) ($this->option_media_active ?? false);
    }

    public function optionStockRowFor(?string $variant = null, ?string $size = null): ?ProductOptionStock
    {
        $variant = ProductOptionStock::normalizeOption($variant);
        $size = ProductOptionStock::normalizeOption($size);

        $row = $this->getOptionStockRow($variant, $size);

        if (!$row) {
            $row = $this->getOptionStockRow($variant, '');
        }

        if (!$row) {
            $row = $this->getOptionStockRow('', $size);
        }

        if (!$row) {
            $row = $this->getOptionStockRow('', '');
        }

        return $row;
    }

    public function sellingPriceFor(?string $variant = null, ?string $size = null): int
    {
        $basePrice = $this->sellingPrice();

        if (!$this->usesOptionPricing()) {
            return $basePrice;
        }

        $row = $this->optionStockRowFor($variant, $size);

        if (!$row || !$row->hasCustomPrice()) {
            return $basePrice;
        }

        return $row->sellingPrice($basePrice);
    }

    public function originalPriceFor(?string $variant = null, ?string $size = null): ?int
    {
        if (!$this->usesOptionPricing()) {
            return $this->originalPrice();
        }

        $row = $this->optionStockRowFor($variant, $size);

        if (!$row || !$row->hasCustomPrice()) {
            return $this->originalPrice();
        }

        return $row->originalPrice();
    }

    public function discountPercentageFor(?string $variant = null, ?string $size = null): ?int
    {
        if (!$this->usesOptionPricing()) {
            return $this->discountPercentage();
        }

        $row = $this->optionStockRowFor($variant, $size);

        if (!$row || !$row->hasCustomPrice()) {
            return $this->discountPercentage();
        }

        return $row->discountPercentage();
    }

    public function imageForOption(?string $variant = null, ?string $size = null): ?string
    {
        if (!$this->usesOptionMedia()) {
            return $this->primary_image;
        }

        $row = $this->optionStockRowFor($variant, $size);

        if ($row && $row->image_path) {
            return $row->image_path;
        }

        return $this->primary_image;
    }

    public function isUpcoming(): bool
    {
        return ($this->product_status ?: 'ready') === 'upcoming';
    }

    public function statusLabel(): ?string
    {
        return match ($this->product_status ?: 'ready') {
            'pre_order' => 'Pre-Order',
            'upcoming' => 'Upcoming',
            default => null,
        };
    }

    public function optionStockTotal(): int
    {
        if (!$this->hasOptionStockRows()) {
            return max(0, (int) ($this->stock ?? 0));
        }

        $rows = $this->relationLoaded('optionStocks')
            ? $this->optionStocks
            : $this->optionStocks()->get();

        return (int) $rows->sum(fn (ProductOptionStock $row) => max(0, (int) $row->stock));
    }

    public function availableStock(): int
    {
        if ($this->isUpcoming()) {
            return 0;
        }

        if ($this->hasOptionStockRows()) {
            return $this->optionStockTotal();
        }

        if (!$this->in_stock) {
            return 0;
        }

        return max(0, (int) ($this->stock ?? 0));
    }

    public function defaultOptionSelection(): array
    {
        $variants = $this->relationLoaded('variants')
            ? $this->variants->pluck('name')->map(fn ($value) => ProductOptionStock::normalizeOption($value))->filter(fn ($value) => $value !== '')->values()->all()
            : $this->variants()->orderBy('sort_order')->pluck('name')->map(fn ($value) => ProductOptionStock::normalizeOption($value))->filter(fn ($value) => $value !== '')->values()->all();

        $sizes = $this->relationLoaded('sizes')
            ? $this->sizes->pluck('size')->map(fn ($value) => ProductOptionStock::normalizeOption($value))->filter(fn ($value) => $value !== '')->values()->all()
            : $this->sizes()->orderBy('sort_order')->pluck('size')->map(fn ($value) => ProductOptionStock::normalizeOption($value))->filter(fn ($value) => $value !== '')->values()->all();

        if (empty($variants)) {
            $variants = [''];
        }

        if (($this->type ?: 'accessory') !== 'apparel' || empty($sizes)) {
            $sizes = [''];
        }

        $first = [
            'variant' => $variants[0] ?? '',
            'size' => $sizes[0] ?? '',
            'stock' => $this->availableStockFor($variants[0] ?? '', $sizes[0] ?? ''),
        ];

        if (!$this->hasOptionStockRows()) {
            $first['stock'] = $this->availableStock();
            return $first;
        }

        foreach ($variants as $variant) {
            foreach ($sizes as $size) {
                $stock = $this->availableStockFor($variant, $size);

                if ($stock > 0) {
                    return [
                        'variant' => $variant,
                        'size' => $size,
                        'stock' => $stock,
                    ];
                }
            }
        }

        return $first;
    }

    public function availableStockFor(?string $variant = null, ?string $size = null): int
    {
        if ($this->isUpcoming()) {
            return 0;
        }

        if (!$this->in_stock) {
            return 0;
        }

        if ($this->hasOptionStockRows()) {
            $variant = ProductOptionStock::normalizeOption($variant);
            $size = ProductOptionStock::normalizeOption($size);

            $row = $this->getOptionStockRow($variant, $size);

            if (!$row) {
                // Fallback aman untuk produk lama: jika hanya ada stok umum,
                // stok umum tetap dipakai walaupun user memilih varian/ukuran.
                $row = $this->getOptionStockRow('', '');
            }

            return $row ? max(0, (int) $row->stock) : 0;
        }

        return $this->availableStock();
    }

    public function hasOptionStockRows(): bool
    {
        if (!$this->relationLoaded('optionStocks')) {
            try {
                if (!Schema::hasTable('product_option_stocks')) {
                    return false;
                }
            } catch (\Throwable $exception) {
                return false;
            }

            return $this->optionStocks()->exists();
        }

        return $this->optionStocks->isNotEmpty();
    }

    public function getOptionStockRow(string $variant, string $size): ?ProductOptionStock
    {
        if ($this->relationLoaded('optionStocks')) {
            return $this->optionStocks->first(function (ProductOptionStock $row) use ($variant, $size) {
                return ProductOptionStock::normalizeOption($row->variant) === $variant
                    && ProductOptionStock::normalizeOption($row->size) === $size;
            });
        }

        try {
            if (!Schema::hasTable('product_option_stocks')) {
                return null;
            }
        } catch (\Throwable $exception) {
            return null;
        }

        return $this->optionStocks()
            ->where('variant', $variant)
            ->where('size', $size)
            ->first();
    }

    public function optionStocksPayload(): array
    {
        if (!$this->hasOptionStockRows()) {
            return [];
        }

        $rows = $this->relationLoaded('optionStocks')
            ? $this->optionStocks
            : $this->optionStocks()->get();

        return $rows
            ->mapWithKeys(function (ProductOptionStock $row) {
                $variant = ProductOptionStock::normalizeOption($row->variant);
                $size = ProductOptionStock::normalizeOption($row->size);
                $key = $variant . '||' . $size;

                return [$key => max(0, (int) $row->stock)];
            })
            ->all();
    }


    public function optionPricesPayload(): array
    {
        if (!$this->usesOptionPricing() || !$this->hasOptionStockRows()) {
            return [];
        }

        $rows = $this->relationLoaded('optionStocks')
            ? $this->optionStocks
            : $this->optionStocks()->get();

        return $rows
            ->filter(fn (ProductOptionStock $row) => $row->hasCustomPrice())
            ->mapWithKeys(function (ProductOptionStock $row) {
                $variant = ProductOptionStock::normalizeOption($row->variant);
                $size = ProductOptionStock::normalizeOption($row->size);
                $key = $variant . '||' . $size;

                return [$key => [
                    'price' => $row->sellingPrice($this->sellingPrice()),
                    'base_price' => (int) $row->price,
                    'original_price' => $row->originalPrice(),
                    'discount_price' => $row->hasActiveDiscount() ? (int) $row->discount_price : null,
                    'has_discount' => $row->hasActiveDiscount(),
                    'discount_percentage' => $row->discountPercentage(),
                ]];
            })
            ->all();
    }

    public function optionMediaPayload(): array
    {
        if (!$this->usesOptionMedia() || !$this->hasOptionStockRows()) {
            return [];
        }

        $rows = $this->relationLoaded('optionStocks')
            ? $this->optionStocks
            : $this->optionStocks()->get();

        return $rows
            ->filter(fn (ProductOptionStock $row) => !empty($row->image_path))
            ->mapWithKeys(function (ProductOptionStock $row) {
                $variant = ProductOptionStock::normalizeOption($row->variant);
                $size = ProductOptionStock::normalizeOption($row->size);
                $key = $variant . '||' . $size;

                return [$key => $row->image_path];
            })
            ->all();
    }

    public function optionPriceRangePayload(): array
    {
        $prices = [];

        if ($this->usesOptionPricing() && $this->hasOptionStockRows()) {
            $rows = $this->relationLoaded('optionStocks')
                ? $this->optionStocks
                : $this->optionStocks()->get();

            foreach ($rows as $row) {
                if ($row->hasCustomPrice()) {
                    $prices[] = $row->sellingPrice($this->sellingPrice());
                }
            }
        }

        if (empty($prices)) {
            $prices[] = $this->sellingPrice();
        }

        $prices = array_values(array_unique(array_map(fn ($price) => max(0, (int) $price), $prices)));

        return [
            'min' => min($prices),
            'max' => max($prices),
            'has_range' => min($prices) !== max($prices),
        ];
    }

    public function isAvailableForPurchase(): bool
    {
        return $this->availableStock() > 0;
    }

    public function toCatalogArray(): array
    {
        $images = $this->images->pluck('path')->values()->all();
        $variants = $this->variants->pluck('name')->values()->all();
        $sizes = $this->sizes->pluck('size')->values()->all();
        $availableStock = $this->availableStock();
        $defaultOption = $this->defaultOptionSelection();
        $optionPriceRange = $this->optionPriceRangePayload();
        $optionPrices = $this->optionPricesPayload();
        $optionMedia = $this->optionMediaPayload();
        $optionDiscountPercentage = collect($optionPrices)->pluck('discount_percentage')->filter()->max();
        $hasAnyDiscount = $this->hasActiveDiscount() || (bool) $optionDiscountPercentage;
        $discountPercentage = $optionDiscountPercentage ?: $this->discountPercentage();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $optionPriceRange['min'],
            'base_price' => (int) $this->price,
            'default_price' => $this->sellingPrice(),
            'default_original_price' => $this->originalPrice(),
            'default_has_discount' => $this->hasActiveDiscount(),
            'display_price' => $optionPriceRange['min'],
            'price_min' => $optionPriceRange['min'],
            'price_max' => $optionPriceRange['max'],
            'has_price_range' => $optionPriceRange['has_range'],
            'original_price' => $this->originalPrice(),
            'discount_price' => $this->hasActiveDiscount() ? (int) $this->discount_price : null,
            'discount_active' => $this->hasActiveDiscount(),
            'has_discount' => $hasAnyDiscount,
            'discount_percentage' => $discountPercentage,
            'product_status' => $this->product_status ?: 'ready',
            'status_label' => $this->statusLabel(),
            'is_upcoming' => $this->isUpcoming(),
            'can_add_to_cart' => !$this->isUpcoming() && $availableStock > 0,
            'default_variant' => $defaultOption['variant'] ?? '',
            'default_size' => $defaultOption['size'] ?? '',
            'default_option_stock' => (int) ($defaultOption['stock'] ?? 0),
            'image' => $this->primary_image ?? ($images[0] ?? null),
            'images' => $images ?: [($this->primary_image ?? null)],
            'type' => $this->type,
            'category' => $this->category?->name,
            'category_slug' => $this->category?->slug,
            'variants' => $variants,
            'sizes' => $sizes,
            'sku' => $this->sku,
            'in_stock' => $availableStock > 0,
            'stock' => $availableStock,
            'available_stock' => $availableStock,
            'stored_stock' => (int) ($this->stock ?? 0),
            'option_stocks' => $this->optionStocksPayload(),
            'option_prices' => $optionPrices,
            'option_media' => $optionMedia,
            'option_pricing_active' => $this->usesOptionPricing(),
            'option_media_active' => $this->usesOptionMedia(),
            'has_option_stock' => $this->hasOptionStockRows(),
            'shipping' => $this->shipping,
            'desc' => $this->desc,
            'artist' => $this->artist?->name,
            'artist_slug' => $this->artist?->slug,
            'weight' => $this->weight,
        ];
    }
}
