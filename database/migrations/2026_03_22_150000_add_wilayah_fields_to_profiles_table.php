<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'provinsi')) {
                $table->string('provinsi', 50)->nullable()->after('status_perkawinan');
            }
            if (!Schema::hasColumn('profiles', 'kabupaten')) {
                $table->string('kabupaten', 50)->nullable()->after('provinsi');
            }
            if (!Schema::hasColumn('profiles', 'kecamatan')) {
                $table->string('kecamatan', 50)->nullable()->after('kabupaten');
            }
            if (!Schema::hasColumn('profiles', 'desa')) {
                $table->string('desa', 50)->nullable()->after('kecamatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'desa')) {
                $table->dropColumn('desa');
            }
            if (Schema::hasColumn('profiles', 'kecamatan')) {
                $table->dropColumn('kecamatan');
            }
            if (Schema::hasColumn('profiles', 'kabupaten')) {
                $table->dropColumn('kabupaten');
            }
            if (Schema::hasColumn('profiles', 'provinsi')) {
                $table->dropColumn('provinsi');
            }
        });
    }
};
