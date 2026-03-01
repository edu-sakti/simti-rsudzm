<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('devices', 'room_id')) {
            Schema::table('devices', function (Blueprint $table) {
                // rooms table memakai kolom room_id (string) sebagai primary key
                $table->string('room_id', 20)->nullable()->after('id');
                $table->foreign('room_id')->references('room_id')->on('rooms')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
        });
    }
};
