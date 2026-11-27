<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_receipt_path')) {
                $table->string('shipping_receipt_path')->nullable()->after('tracking_notes');
            }

            if (!Schema::hasColumn('orders', 'shipping_receipt_uploaded_at')) {
                $table->timestamp('shipping_receipt_uploaded_at')->nullable()->after('shipping_receipt_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_receipt_uploaded_at')) {
                $table->dropColumn('shipping_receipt_uploaded_at');
            }

            if (Schema::hasColumn('orders', 'shipping_receipt_path')) {
                $table->dropColumn('shipping_receipt_path');
            }
        });
    }
};
