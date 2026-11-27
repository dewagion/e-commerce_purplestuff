<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('products', function (Blueprint $table) {
      $table->string('id')->primary(); // prod-tee-001
      $table->foreignId('artist_id')->constrained('artists')->cascadeOnDelete();
      $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();

      $table->string('name');
      $table->string('slug')->nullable()->unique();
      $table->string('sku')->nullable();
      $table->enum('type', ['apparel', 'accessory'])->default('accessory');
      $table->unsignedBigInteger('price');
      $table->text('desc')->nullable();
      $table->string('shipping')->nullable();
      $table->boolean('in_stock')->default(true);

      $table->string('primary_image')->nullable(); // ambil images[0]
      $table->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('products');
  }
};