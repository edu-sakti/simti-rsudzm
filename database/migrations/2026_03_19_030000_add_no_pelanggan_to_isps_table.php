<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('isps', function (Blueprint $table) {
            if (!Schema::hasColumn('isps', 'no_pelanggan')) {
                $table->string('no_pelanggan', 100)->after('nama_isp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('isps', function (Blueprint $table) {
            if (Schema::hasColumn('isps', 'no_pelanggan')) {
                $table->dropColumn('no_pelanggan');
            }
        });
    }
};
