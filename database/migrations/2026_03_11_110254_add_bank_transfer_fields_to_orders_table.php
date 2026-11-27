<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('bank_transfer')->after('total_items');
            }

            if (!Schema::hasColumn('orders', 'payment_label')) {
                $table->string('payment_label')->default('Transfer Bank')->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('payment_label');
            }

            if (!Schema::hasColumn('orders', 'payment_code')) {
                $table->string('payment_code')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_code')) {
                $table->dropColumn('payment_code');
            }

            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }

            if (Schema::hasColumn('orders', 'payment_label')) {
                $table->dropColumn('payment_label');
            }

            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};