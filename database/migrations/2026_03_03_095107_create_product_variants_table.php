<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('product_variants', function (Blueprint $table) {
      $table->id();
      $table->string('product_id');
      $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

      $table->string('name');
      $table->unsignedInteger('sort_order')->default(0);
      $table->timestamps();

      $table->index(['product_id', 'sort_order']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('product_variants');
  }
};