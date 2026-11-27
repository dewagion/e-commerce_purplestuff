<?php

use Illuminate\Database\Migrations\Migration;

/*
|--------------------------------------------------------------------------
| Default Banner Seeder Migration Disabled
|--------------------------------------------------------------------------
|
| File migration ini sebelumnya menambahkan banner template/template.
|
| Sekarang banner dashboard harus dibuat dari Admin -> Banner Dashboard agar
| data banner tidak lagi menjadi template hardcoded di code.
|
| Migration ini dipertahankan sebagai no-op agar project yang sudah memiliki
| riwayat migration lama tetap aman dan tidak error.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        // Tidak lagi membuat banner template.
    }

    public function down(): void
    {
        // Tidak ada data yang perlu dibalik.
    }
};
