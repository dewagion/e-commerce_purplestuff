<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'discount_price')) {
                $table->unsignedInteger('discount_price')->nullable()->after('price');
            }

            if (!Schema::hasColumn('products', 'discount_active')) {
                $table->boolean('discount_active')->default(false)->after('discount_price');
            }

            if (!Schema::hasColumn('products', 'product_status')) {
                $table->string('product_status', 30)->default('ready')->after('discount_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'product_status')) {
                $table->dropColumn('product_status');
            }

            if (Schema::hasColumn('products', 'discount_active')) {
                $table->dropColumn('discount_active');
            }

            if (Schema::hasColumn('products', 'discount_price')) {
                $table->dropColumn('discount_price');
            }
        });
    }
};
