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
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_expired_at')) {
                $table->dropColumn('payment_expired_at');
            }

            if (Schema::hasColumn('orders', 'payment_deadline_at')) {
                $table->dropColumn('payment_deadline_at');
            }
        });
    }
};
