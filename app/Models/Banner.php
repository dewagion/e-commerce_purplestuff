<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'badge_text',
        'image',
        'dashboard_section',
        'cta_text',
        'cta_link',
        'target_type',
        'target_category_slug',
        'target_artist_slug',
        'target_product_ids',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'target_product_ids' => 'array',
    ];

    public function getTargetProductIdsListAttribute(): array
    {
        $ids = $this->target_product_ids;

        if (is_string($ids)) {
            $decoded = json_decode($ids, true);
            $ids = is_array($decoded) ? $decoded : [];
        }

        return collect($ids ?? [])
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getResolvedTargetTypeAttribute(): string
    {
        $type = (string) ($this->target_type ?: '');

        if ($type === 'manual') {
            return !empty($this->target_product_ids_list) ? 'products' : 'all';
        }

        if (in_array($type, ['all', 'category', 'artist', 'products', 'custom'], true)) {
            return $type;
        }

        return !empty($this->target_product_ids_list) ? 'products' : 'all';
    }

    public function getResolvedDashboardSectionAttribute(): string
    {
        $section = (string) ($this->dashboard_section ?: 'hero');

        if (in_array($section, ['hero', 'category_grid', 'single_feature'], true)) {
            return $section;
        }

        return 'hero';
    }

    public static function dashboardSectionOptions(): array
    {
        return [
            'hero' => 'Hero Slider Utama',
            'category_grid' => 'Banner Kartu 3 Kolom',
            'single_feature' => 'Single Highlight Banner',
        ];
    }
}
