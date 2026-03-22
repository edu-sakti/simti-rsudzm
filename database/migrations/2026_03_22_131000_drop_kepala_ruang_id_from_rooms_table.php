<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasColumn = Schema::hasColumn('rooms', 'kepala_ruang_id');
        $fkName = null;
        if ($hasColumn) {
            $row = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'rooms'
                   AND COLUMN_NAME = 'kepala_ruang_id'
                   AND REFERENCED_TABLE_NAME IS NOT NULL"
            );
            $fkName = $row?->CONSTRAINT_NAME ?? null;
        }

        Schema::table('rooms', function (Blueprint $table) use ($hasColumn, $fkName) {
            if ($hasColumn) {
                if ($fkName) {
                    $table->dropForeign($fkName);
                }
                $table->dropColumn('kepala_ruang_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'kepala_ruang_id')) {
                $table->unsignedBigInteger('kepala_ruang_id')->nullable()->after('name');
                $table->foreign('kepala_ruang_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }
};
