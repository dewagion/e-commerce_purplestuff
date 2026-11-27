<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('payment_code');
            }

            if (!Schema::hasColumn('orders', 'payment_proof_uploaded_at')) {
                $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_proof_uploaded_at')) {
                $table->dropColumn('payment_proof_uploaded_at');
            }

            if (Schema::hasColumn('orders', 'payment_proof_path')) {
                $table->dropColumn('payment_proof_path');
            }
        });
    }
};