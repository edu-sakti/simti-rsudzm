<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 100)->nullable()->after('phone');
            });
        }

        if (
            Schema::hasTable('users') &&
            Schema::hasColumn('users', 'role') &&
            Schema::hasColumn('users', 'role_id') &&
            Schema::hasTable('roles')
        ) {
            DB::statement("
                UPDATE users u
                LEFT JOIN roles r ON r.id = u.role_id
                SET u.role = COALESCE(r.name, u.role)
            ");
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->dropForeign(['role_id']);
                } catch (\Throwable $e) {
                    // Ignore when foreign key is already absent.
                }
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        }

        if (Schema::hasTable('role_permissions') && Schema::hasColumn('role_permissions', 'role_id')) {
            Schema::table('role_permissions', function (Blueprint $table) {
                try {
                    $table->dropForeign(['role_id']);
                } catch (\Throwable $e) {
                    // Ignore when foreign key is already absent.
                }
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::drop('roles');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable()->after('role');
            });
        }
    }
};

