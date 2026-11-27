<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method varchar(255) NOT NULL DEFAULT 'bank_transfer'");
        DB::statement("ALTER TABLE orders MODIFY payment_label varchar(255) NOT NULL DEFAULT 'Transfer Bank'");
        DB::statement("ALTER TABLE orders MODIFY payment_status varchar(255) NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY status varchar(255) NOT NULL DEFAULT 'Menunggu Pembayaran'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method varchar(255) NOT NULL DEFAULT 'cod'");
        DB::statement("ALTER TABLE orders MODIFY payment_label varchar(255) NOT NULL DEFAULT 'COD'");
        DB::statement("ALTER TABLE orders MODIFY payment_status varchar(255) NOT NULL DEFAULT 'unpaid'");
        DB::statement("ALTER TABLE orders MODIFY status varchar(255) NOT NULL DEFAULT 'Menunggu Konfirmasi'");
    }
};