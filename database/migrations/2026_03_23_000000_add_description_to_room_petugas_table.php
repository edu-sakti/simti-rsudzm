<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_petugas', function (Blueprint $table) {
            $table->text('description')->nullable()->after('room_id');
        });
    }

    public function down(): void
    {
        Schema::table('room_petugas', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
