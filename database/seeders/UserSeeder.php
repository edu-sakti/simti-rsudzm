<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin SIMTI',
                'username' => 'admin',
                'role' => 'admin',
                'email' => 'admin@simti.net',
                'password' => Hash::make('admin123'), 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
