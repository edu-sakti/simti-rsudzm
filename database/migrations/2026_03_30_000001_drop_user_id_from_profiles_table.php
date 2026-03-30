<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('profiles') || !Schema::hasColumn('profiles', 'user_id')) {
            return;
        }

        // Migrasi relasi profile -> user ke pola profiles.id = users.id
        DB::statement('UPDATE profiles SET id = id + 1000000000');
        DB::statement('UPDATE profiles SET id = user_id');

        $schema = DB::getDatabaseName();
        $foreignKeys = DB::select(
            'SELECT CONSTRAINT_NAME as constraint_name FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$schema, 'profiles', 'user_id']
        );

        Schema::table('profiles', function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk->constraint_name);
            }
        });

        if (Schema::hasColumn('profiles', 'user_id')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('profiles') || Schema::hasColumn('profiles', 'user_id')) {
            return;
        }

        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        DB::statement('UPDATE profiles SET user_id = id WHERE user_id IS NULL');

        Schema::table('profiles', function (Blueprint $table) {
            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
