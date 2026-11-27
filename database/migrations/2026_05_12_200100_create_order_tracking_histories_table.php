<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_tracking_histories')) {
            Schema::create('order_tracking_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tracking_number', 100)->nullable();
                $table->text('tracking_notes')->nullable();
                $table->string('status_snapshot', 100)->nullable();
                $table->string('event_type', 50)->default('tracking_updated');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking_histories');
    }
};