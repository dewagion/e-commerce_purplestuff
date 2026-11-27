<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_deadline_at')) {
                $table->timestamp('payment_deadline_at')->nullable()->after('payment_code')->index();
            }

            if (!Schema::hasColumn('orders', 'payment_expired_at')) {
                $table->timestamp('payment_expired_at')->nullable()->after('payment_deadline_at')->index();
            }

            if (!Schema::hasColumn('orders', 'stock_reserved_at')) {
                $table->timestamp('stock_reserved_at')->nullable()->after('completed_at')->index();
            }

            if (!Schema::hasColumn('orders', 'stock_released_at')) {
                $table->timestamp('stock_released_at')->nullable()->after('stock_reserved_at')->index();
            }

            if (!Schema::hasColumn('orders', 'stock_release_reason')) {
                $table->string('stock_release_reason')->nullable()->after('stock_released_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'stock_release_reason')) {
                $table->dropColumn('stock_release_reason');
            }

            if (Schema::hasColumn('orders', 'stock_released_at')) {
                $table->dropColumn('stock_released_at');
            }

            if (Schema::hasColumn('orders', 'stock_reserved_at')) {
                $table->dropColumn('stock_reserved_at');
            }

            if (Schema::hasColumn('orders', 'payment_expired_at')) {
                $table->dropColumn('payment_expired_at');
            }

            if (Schema::hasColumn('orders', 'payment_deadline_at')) {
                $table->dropColumn('payment_deadline_at');
            }
        });
    }
};
