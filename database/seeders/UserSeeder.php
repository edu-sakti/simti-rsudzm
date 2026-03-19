<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->delete();

        DB::table('users')->insert([
            [
                'name' => 'Muhammad Syahputra',
                'username' => 'putra',
                'phone' => '6285372985440',
                'role' => 'petugas_it',
                'is_admin' => 1,
                'is_verified' => 1,
                'room_id' => null,
                'jabatan_id' => null,
                'email' => 'putra@simti.xyz',
                'password' => Hash::make('putra123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Noval Saputra',
                'username' => 'noval',
                'phone' => '628123456789',
                'role' => 'petugas_it',
                'is_admin' => 0,
                'is_verified' => 1,
                'room_id' => null,
                'jabatan_id' => null,
                'email' => 'petugas@simti.xyz',
                'password' => Hash::make('noval123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
