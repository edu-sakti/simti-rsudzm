<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK if exists (MySQL 5/8 compatible)
        $constraint = \DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'devices'
              AND COLUMN_NAME = 'room_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        if ($constraint) {
            \DB::statement("ALTER TABLE devices DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }

        // Drop column if exists
        if (Schema::hasColumn('devices', 'room_id')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('room_id');
            });
        }

        // Recreate column as string FK to rooms.room_id
        Schema::table('devices', function (Blueprint $table) {
            $table->string('room_id', 20)->nullable()->after('id');
            $table->foreign('room_id')->references('room_id')->on('rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Drop FK if exists
        $constraint = \DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'devices'
              AND COLUMN_NAME = 'room_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        if ($constraint) {
            \DB::statement("ALTER TABLE devices DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }

        // Drop column
        if (Schema::hasColumn('devices', 'room_id')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('room_id');
            });
        }

        // Restore as bigint FK to rooms.id (original)
        Schema::table('devices', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('id')->constrained('rooms')->nullOnDelete();
        });
    }
};
