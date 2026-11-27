<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Migration ini sebelumnya duplikat dengan:
     * 2026_03_02_123223_add_phone_address_to_users_table.php
     *
     * Dibuat no-op supaya aman untuk fresh migrate tanpa mencoba menambah kolom yang sama dua kali.
     * Jika migration ini sudah pernah jalan di database Anda, perubahan file ini tidak akan mengubah data apa pun.
     */
    public function up(): void
    {
        // No-op: kolom phone dan address sudah ditangani migration 2026_03_02_123223.
    }

    public function down(): void
    {
        // No-op: jangan drop kolom dari migration duplikat.
    }
};
