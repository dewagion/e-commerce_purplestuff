<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number', 100)->nullable()->after('shipping_destination_postal_code');
            }

            if (!Schema::hasColumn('orders', 'tracking_notes')) {
                $table->text('tracking_notes')->nullable()->after('tracking_number');
            }

            if (!Schema::hasColumn('orders', 'tracking_updated_at')) {
                $table->timestamp('tracking_updated_at')->nullable()->after('tracking_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $drops = [];

            foreach (['tracking_updated_at', 'tracking_notes', 'tracking_number'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $drops[] = $column;
                }
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};