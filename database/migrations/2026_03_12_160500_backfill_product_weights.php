<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->where('id', 'prod-tee-001')->update(['weight' => 250]);
        DB::table('products')->where('id', 'prod-hood-002')->update(['weight' => 700]);
        DB::table('products')->where('id', 'prod-pc-003')->update(['weight' => 100]);
        DB::table('products')->where('id', 'prod-cap-004')->update(['weight' => 300]);
    }

    public function down(): void
    {
        DB::table('products')->whereIn('id', [
            'prod-tee-001',
            'prod-hood-002',
            'prod-pc-003',
            'prod-cap-004',
        ])->update(['weight' => 0]);
    }
};
