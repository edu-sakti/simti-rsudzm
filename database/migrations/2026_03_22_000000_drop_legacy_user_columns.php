<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'room_id')) {
                try {
                    $table->dropForeign(['room_id']);
                } catch (\Throwable $e) {
                    // Abaikan jika foreign key sudah tidak ada.
                }
                $table->dropColumn('room_id');
            }

            if (Schema::hasColumn('users', 'jabatan_id')) {
                $table->dropColumn('jabatan_id');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('petugas_it')->after('username');
            }

            if (!Schema::hasColumn('users', 'room_id')) {
                $table->unsignedBigInteger('room_id')->nullable()->after('role_id');
                $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'jabatan_id')) {
                $table->string('jabatan_id', 255)->nullable()->after('room_id');
            }
        });
    }
};

