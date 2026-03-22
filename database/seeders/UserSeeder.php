<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->delete();

        $baseRoles = [
            ['name' => 'Admin', 'description' => 'Akses penuh aplikasi'],
            ['name' => 'Petugas IT', 'description' => 'Pengelola teknis'],
            ['name' => 'Petugas Helpdesk', 'description' => 'Petugas helpdesk'],
            ['name' => 'Manajemen', 'description' => 'Manajemen'],
            ['name' => 'Kepala Ruangan', 'description' => 'Kepala ruangan'],
        ];

        foreach ($baseRoles as $role) {
            $exists = DB::table('roles')
                ->whereRaw('LOWER(name) = ?', [strtolower($role['name'])])
                ->exists();
            if (!$exists) {
                DB::table('roles')->insert([
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $roles = DB::table('roles')->get()->keyBy(function ($role) {
            return Str::slug($role->name, '_');
        });
        $petugasItId = $roles['petugas_it']->id ?? null;

        DB::table('users')->insert([
            [
                'name' => 'Muhammad Syahputra',
                'username' => 'putra',
                'phone' => '6285372985440',
                'role_id' => $petugasItId,
                'is_admin' => 1,
                'is_verified' => 1,
                'email' => 'putra@simti.xyz',
                'password' => Hash::make('putra123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Noval Saputra',
                'username' => 'noval',
                'phone' => '628123456789',
                'role_id' => $petugasItId,
                'is_admin' => 0,
                'is_verified' => 1,
                'email' => 'petugas@simti.xyz',
                'password' => Hash::make('noval123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
