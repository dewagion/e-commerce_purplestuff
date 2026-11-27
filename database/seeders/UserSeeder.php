<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'address' => 'Alamat admin demo',
                'shipping_postal_code' => '80582',
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer Demo',
                'password' => Hash::make('password'),
                'phone' => '081234567891',
                'address' => 'Alamat customer demo',
                'shipping_postal_code' => '83663',
                'is_admin' => false,
            ]
        );
    }
}
