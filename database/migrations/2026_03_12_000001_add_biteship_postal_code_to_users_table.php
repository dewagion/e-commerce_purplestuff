<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'shipping_postal_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('shipping_postal_code', 10)->nullable()->after('address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'shipping_postal_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('shipping_postal_code');
            });
        }
    }
};
