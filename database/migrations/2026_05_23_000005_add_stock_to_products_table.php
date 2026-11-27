<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        if (!Schema::hasColumn('products', 'stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('stock')->default(0)->after('in_stock');
            });
        }

        // Supaya produk lama tidak langsung dianggap habis setelah migrasi.
        // Admin tetap bisa mengubah stok asli satu per satu dari halaman edit produk.
        DB::table('products')
            ->where('in_stock', true)
            ->where('stock', 0)
            ->update(['stock' => 99]);
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('stock');
            });
        }
    }
};
