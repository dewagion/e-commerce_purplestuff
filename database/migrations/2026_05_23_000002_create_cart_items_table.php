<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cart_items')) {
            return;
        }

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('product_id');
            $table->string('key', 191);
            $table->string('size')->nullable();
            $table->string('variant')->nullable();
            $table->unsignedTinyInteger('qty')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'key']);
            $table->index(['user_id', 'product_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
