<?php

namespace App\Support;

/*
|--------------------------------------------------------------------------
| Catalog Demo Data
|--------------------------------------------------------------------------
|
| Data produk, kategori, artist, dan banner tidak lagi disimpan sebagai
| template hardcoded di code. Semua data toko sebaiknya dibuat dan dikelola
| dari admin/database.
|
| Class ini dipertahankan agar import lama tidak error, tetapi tidak lagi
| mengembalikan produk/kategori/artist demo.
|
*/

class Catalog
{
    public static function categories(): array
    {
        return [];
    }

    public static function artists(): array
    {
        return [];
    }

    public static function products(): array
    {
        return [];
    }
}
