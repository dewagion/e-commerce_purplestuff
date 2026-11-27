<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Cleanup Default Template Catalog Data
|--------------------------------------------------------------------------
|
| Migration ini membersihkan data contoh bawaan yang sangat spesifik dari
| versi template lama. Data yang dihapus hanya data default yang dikenali
| dari ID/gambar/nama bawaan.
|
| Produk, banner, kategori, dan artist baru yang dibuat dari admin tidak
| akan disentuh.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        $this->deleteDefaultBanners();
        $this->deleteDefaultProducts();
        $this->deleteUnusedDefaultCategories();
        $this->deleteUnusedDefaultArtists();
    }

    public function down(): void
    {
        // Data demo tidak dibuat ulang agar project tetap bersih.
    }

    private function deleteDefaultBanners(): void
    {
        if (!Schema::hasTable('banners')) {
            return;
        }

        DB::table('banners')
            ->whereIn('image', [
                '/images/banners/slide-1.jpg',
                '/images/banners/slide-2.jpg',
                '/images/banners/slide-3.jpg',
            ])
            ->orWhereIn('title', [
                'New Collection 2026',
                'Fandom Starter Pack',
                'Lightstick & Outfit',
            ])
            ->delete();
    }

    private function deleteDefaultProducts(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        DB::table('products')
            ->whereIn('id', [
                'prod-tee-001',
                'prod-hood-002',
                'prod-pc-003',
                'prod-cap-004',
            ])
            ->orWhereIn('primary_image', [
                '/images/products/classic-white-tee.jpg',
                '/images/products/purple-hoodie.jpg',
                '/images/products/photocard-set.jpg',
                '/images/products/baseball-cap.jpg',
            ])
            ->orWhereIn('name', [
                'Classic White Tee',
                'Purple Hoodie',
                'Photo Card Set',
                'Baseball Cap',
            ])
            ->delete();
    }

    private function deleteUnusedDefaultCategories(): void
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        $defaultSlugs = [
            'lightstick',
            'photocard',
            'album',
            'tshirts',
            'hoodies',
            'accessories',
        ];

        $query = DB::table('categories')->whereIn('slug', $defaultSlugs);

        if (Schema::hasTable('products')) {
            $query->whereNotExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('products')
                    ->whereColumn('products.category_id', 'categories.id');
            });
        }

        $query->delete();
    }

    private function deleteUnusedDefaultArtists(): void
    {
        if (!Schema::hasTable('artists')) {
            return;
        }

        $defaultSlugs = [
            'txt',
            'bts',
            'blackpink',
            'enhypen',
        ];

        $query = DB::table('artists')->whereIn('slug', $defaultSlugs);

        if (Schema::hasTable('products')) {
            $query->whereNotExists(function ($subquery) {
                $subquery->selectRaw('1')
                    ->from('products')
                    ->whereColumn('products.artist_id', 'artists.id');
            });
        }

        $query->delete();
    }
};
