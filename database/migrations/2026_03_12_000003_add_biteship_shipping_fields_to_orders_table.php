<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_courier')) {
                $table->string('shipping_courier')->nullable()->after('total_items');
            }
            if (!Schema::hasColumn('orders', 'shipping_courier_name')) {
                $table->string('shipping_courier_name')->nullable()->after('shipping_courier');
            }
            if (!Schema::hasColumn('orders', 'shipping_service_code')) {
                $table->string('shipping_service_code')->nullable()->after('shipping_courier_name');
            }
            if (!Schema::hasColumn('orders', 'shipping_service_name')) {
                $table->string('shipping_service_name')->nullable()->after('shipping_service_code');
            }
            if (!Schema::hasColumn('orders', 'shipping_etd')) {
                $table->string('shipping_etd')->nullable()->after('shipping_service_name');
            }
            if (!Schema::hasColumn('orders', 'shipping_weight')) {
                $table->unsignedInteger('shipping_weight')->default(0)->after('shipping_etd');
            }
            if (!Schema::hasColumn('orders', 'shipping_origin_postal_code')) {
                $table->string('shipping_origin_postal_code', 10)->nullable()->after('shipping_weight');
            }
            if (!Schema::hasColumn('orders', 'shipping_destination_postal_code')) {
                $table->string('shipping_destination_postal_code', 10)->nullable()->after('shipping_origin_postal_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $drops = [];
            foreach ([
                'shipping_courier',
                'shipping_courier_name',
                'shipping_service_code',
                'shipping_service_name',
                'shipping_etd',
                'shipping_weight',
                'shipping_origin_postal_code',
                'shipping_destination_postal_code',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $drops[] = $column;
                }
            }
            if ($drops) {
                $table->dropColumn($drops);
            }
        });
    }
};
