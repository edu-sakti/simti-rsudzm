<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('users')) {
            return;
        }

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

        if (!Schema::hasColumn('users', 'role_id')) {
            return;
        }

        $roles = DB::table('roles')->get()->keyBy(function ($role) {
            return Str::slug($role->name, '_');
        });

        if (!Schema::hasColumn('users', 'role')) {
            return;
        }

        DB::table('users')->orderBy('id')->chunkById(200, function ($rows) use ($roles) {
            foreach ($rows as $row) {
                if (!empty($row->role_id)) {
                    continue;
                }

                $roleKey = Str::slug((string) $row->role, '_');
                if (in_array($roleKey, ['petugas', 'staff'], true)) {
                    $roleKey = 'petugas_it';
                }
                $isAdmin = ($row->is_admin ?? false);
                if ($roleKey === 'admin') {
                    $isAdmin = true;
                    $roleKey = 'petugas_it';
                }

                $roleId = $roles[$roleKey]->id ?? null;
                if ($roleId) {
                    DB::table('users')->where('id', $row->id)->update([
                        'role_id' => $roleId,
                        'is_admin' => $isAdmin,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role_id')) {
            return;
        }

        DB::table('users')->update(['role_id' => null]);
    }
};
