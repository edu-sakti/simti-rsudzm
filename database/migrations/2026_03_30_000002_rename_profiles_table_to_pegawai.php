<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('profiles') && !Schema::hasTable('pegawai')) {
            Schema::rename('profiles', 'pegawai');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pegawai') && !Schema::hasTable('profiles')) {
            Schema::rename('pegawai', 'profiles');
        }
    }
};

