<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
         DB::table('users')->updateOrInsert(
            ['username' => 'admin'], // pengecekan data
            [
                'name' => 'Admin SIMTI',
                'role' => 'admin',
                'phone' => '6281234567890',
                'room_id' => null,
                'is_verified' => 1,
                'email' => 'admin@simti.xyz',
                'password' => Hash::make('admin123'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
