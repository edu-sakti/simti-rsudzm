<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('room_petugas', 'is_kepala')) {
            Schema::table('room_petugas', function (Blueprint $table) {
                $table->dropColumn('is_kepala');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('room_petugas', 'is_kepala')) {
            Schema::table('room_petugas', function (Blueprint $table) {
                $table->boolean('is_kepala')->default(false)->after('room_id');
            });
        }
    }
};
