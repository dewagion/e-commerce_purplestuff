<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->string('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->string('sku')->nullable();
            $table->string('variant')->nullable();
            $table->string('size')->nullable();
            $table->string('type')->nullable();

            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('line_total')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};