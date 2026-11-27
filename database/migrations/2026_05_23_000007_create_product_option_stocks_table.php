<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_option_stocks')) {
            Schema::create('product_option_stocks', function (Blueprint $table) {
                $table->id();
                $table->string('product_id');
                $table->string('variant')->default('');
                $table->string('size')->default('');
                $table->unsignedInteger('stock')->default(0);
                $table->timestamps();

                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();

                $table->unique(['product_id', 'variant', 'size'], 'product_option_stocks_unique_option');
                $table->index(['product_id', 'stock'], 'product_option_stocks_product_stock_idx');
            });
        }

        // Jika tabel stok global sudah ada, isi stok opsi awal untuk produk lama yang belum punya stok per opsi.
        // Ini membuat fitur baru aman: produk lama tetap bisa dibeli sesuai stok global yang sudah ada.
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'stock') && Schema::hasTable('product_option_stocks')) {
            $products = DB::table('products')
                ->select('id', 'stock')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('product_option_stocks')
                        ->whereColumn('product_option_stocks.product_id', 'products.id');
                })
                ->get();

            foreach ($products as $product) {
                DB::table('product_option_stocks')->insert([
                    'product_id' => $product->id,
                    'variant' => '',
                    'size' => '',
                    'stock' => max(0, (int) ($product->stock ?? 0)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_stocks');
    }
};
