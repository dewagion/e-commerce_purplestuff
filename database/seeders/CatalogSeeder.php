<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/*
|--------------------------------------------------------------------------
| CatalogSeeder
|--------------------------------------------------------------------------
|
| Seeder ini sengaja dibuat kosong.
|
| Sebelumnya produk, kategori, dan artist demo diisi dari App\Support\Catalog.
| Untuk project ini, produk/kategori/artist sebaiknya dibuat dari halaman admin
| agar code tidak panjang dan data toko tidak bercampur dengan template.
|
| Jika suatu saat Anda ingin membuat demo data untuk testing, buat seeder baru
| terpisah, misalnya DemoCatalogSeeder, lalu panggil hanya saat development.
|
*/

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Tidak ada data demo yang dibuat otomatis.
    }
}
