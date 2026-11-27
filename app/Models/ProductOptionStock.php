<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionStock extends Model
{
    protected $fillable = [
        'product_id',
        'variant',
        'size',
        'stock',
        'price',
        'discount_price',
        'discount_active',
        'image_path',
    ];

    protected $casts = [
        'stock' => 'integer',
        'price' => 'integer',
        'discount_price' => 'integer',
        'discount_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public static function normalizeOption(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' || $value === '-' ? '' : $value;
    }

    public function getVariantLabelAttribute(): string
    {
        return $this->variant !== '' ? $this->variant : '-';
    }

    public function getSizeLabelAttribute(): string
    {
        return $this->size !== '' ? $this->size : '-';
    }

    public function hasCustomPrice(): bool
    {
        return $this->price !== null && (int) $this->price > 0;
    }

    public function hasActiveDiscount(): bool
    {
        return $this->hasCustomPrice()
            && (bool) $this->discount_active
            && $this->discount_price !== null
            && (int) $this->discount_price > 0
            && (int) $this->discount_price < (int) $this->price;
    }

    public function sellingPrice(?int $fallback = null): int
    {
        if ($this->hasActiveDiscount()) {
            return (int) $this->discount_price;
        }

        if ($this->hasCustomPrice()) {
            return (int) $this->price;
        }

        return max(0, (int) ($fallback ?? 0));
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
}
