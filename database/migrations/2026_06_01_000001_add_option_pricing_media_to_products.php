<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'option_pricing_active')) {
                    $table->boolean('option_pricing_active')->default(false)->after('product_status');
                }

                if (!Schema::hasColumn('products', 'option_media_active')) {
                    $table->boolean('option_media_active')->default(false)->after('option_pricing_active');
                }
            });
        }

        if (Schema::hasTable('product_option_stocks')) {
            Schema::table('product_option_stocks', function (Blueprint $table) {
                if (!Schema::hasColumn('product_option_stocks', 'price')) {
                    $table->unsignedInteger('price')->nullable()->after('stock');
                }

                if (!Schema::hasColumn('product_option_stocks', 'discount_price')) {
                    $table->unsignedInteger('discount_price')->nullable()->after('price');
                }

                if (!Schema::hasColumn('product_option_stocks', 'discount_active')) {
                    $table->boolean('discount_active')->default(false)->after('discount_price');
                }

                if (!Schema::hasColumn('product_option_stocks', 'image_path')) {
                    $table->string('image_path')->nullable()->after('discount_active');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_option_stocks')) {
            Schema::table('product_option_stocks', function (Blueprint $table) {
                foreach (['image_path', 'discount_active', 'discount_price', 'price'] as $column) {
                    if (Schema::hasColumn('product_option_stocks', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                foreach (['option_media_active', 'option_pricing_active'] as $column) {
                    if (Schema::hasColumn('products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
