<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'target_type')) {
                $table->string('target_type', 30)->default('manual')->after('cta_link');
            }

            if (!Schema::hasColumn('banners', 'target_category_slug')) {
                $table->string('target_category_slug')->nullable()->after('target_type');
            }

            if (!Schema::hasColumn('banners', 'target_artist_slug')) {
                $table->string('target_artist_slug')->nullable()->after('target_category_slug');
            }

            if (!Schema::hasColumn('banners', 'target_product_ids')) {
                $table->json('target_product_ids')->nullable()->after('target_artist_slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'target_product_ids')) {
                $table->dropColumn('target_product_ids');
            }

            if (Schema::hasColumn('banners', 'target_artist_slug')) {
                $table->dropColumn('target_artist_slug');
            }

            if (Schema::hasColumn('banners', 'target_category_slug')) {
                $table->dropColumn('target_category_slug');
            }

            if (Schema::hasColumn('banners', 'target_type')) {
                $table->dropColumn('target_type');
            }
        });
    }
};