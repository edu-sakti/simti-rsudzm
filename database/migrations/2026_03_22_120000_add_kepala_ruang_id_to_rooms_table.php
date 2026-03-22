<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'kepala_ruang_id')) {
                $table->unsignedBigInteger('kepala_ruang_id')->nullable()->after('name');
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'kepala_ruang_id')) {
                $table->foreign('kepala_ruang_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'kepala_ruang_id')) {
                $table->dropForeign(['kepala_ruang_id']);
                $table->dropColumn('kepala_ruang_id');
            }
        });
    }
};
