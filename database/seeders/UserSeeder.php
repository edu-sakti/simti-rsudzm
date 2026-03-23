<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'Admin', 'description' => 'Akses penuh sistem'],
            ['name' => 'Petugas IT', 'description' => 'Petugas IT'],
        ];

        $roleIds = [];
        foreach ($roles as $role) {
            $id = DB::table('roles')->where('name', $role['name'])->value('id');
            if (!$id) {
                $id = DB::table('roles')->insertGetId([
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $roleIds[$role['name']] = $id;
        }

        $users = [
            [
                'name' => 'Admin SIMTI',
                'username' => 'admin',
                'phone' => '628551982024',
                'email' => 'admin@simti.net',
                'role_id' => $roleIds['Admin'] ?? null,
                'is_admin' => 1,
                'is_verified' => 1,
                'email_verified_at' => $now,
                'password' => Hash::make('admin123'),
                'remember_token' => Str::random(10),
                'reset_token' => null,
                'reset_token_expired_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Noval Saputra',
                'username' => 'noval',
                'phone' => '6285372985440',
                'email' => 'noval@simti.xyz',
                'role_id' => $roleIds['Petugas IT'] ?? null,
                'is_admin' => 0,
                'is_verified' => 1,
                'email_verified_at' => $now,
                'password' => Hash::make('noval123'),
                'remember_token' => Str::random(10),
                'reset_token' => null,
                'reset_token_expired_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
