<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('order_number')->unique();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->unsignedInteger('total_items')->default(0);

            $table->string('payment_method')->default('cod');
            $table->string('payment_label')->default('COD');
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_code')->nullable();

            $table->string('status')->default('Menunggu Konfirmasi');

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};