<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('role_permissions', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('id');
                $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            }
        });

        if (Schema::hasColumn('role_permissions', 'role') && Schema::hasColumn('role_permissions', 'role_id')) {
            $roles = DB::table('roles')->get()->keyBy(function ($role) {
                return Str::slug($role->name, '_');
            });
            DB::table('role_permissions')->orderBy('id')->chunkById(200, function ($rows) use ($roles) {
                foreach ($rows as $row) {
                    $name = Str::slug($row->role, '_');
                    $roleId = $roles[$name]->id ?? null;
                    if ($roleId) {
                        DB::table('role_permissions')->where('id', $row->id)->update(['role_id' => $roleId]);
                    }
                }
            });
        }

        Schema::table('role_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('role_permissions', 'role')) {
                $table->dropUnique(['role', 'menu']);
                $table->dropColumn('role');
            }
            if (!Schema::hasColumn('role_permissions', 'role')) {
                $table->unique(['role_id', 'menu']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('role_permissions', 'role_id')) {
                $table->dropUnique(['role_id', 'menu']);
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
            if (!Schema::hasColumn('role_permissions', 'role')) {
                $table->string('role', 50)->nullable()->after('id');
                $table->unique(['role', 'menu']);
            }
        });
    }
};
