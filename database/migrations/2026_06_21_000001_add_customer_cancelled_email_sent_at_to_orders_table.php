<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_order_cancelled_email_sent_at')) {
                $table->timestamp('customer_order_cancelled_email_sent_at')
                    ->nullable()
                    ->after('stock_release_reason')
                    ->index('orders_cancelled_email_sent_at_idx');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'customer_order_cancelled_email_sent_at')) {
                $table->dropIndex('orders_cancelled_email_sent_at_idx');
                $table->dropColumn('customer_order_cancelled_email_sent_at');
            }
        });
    }
};
