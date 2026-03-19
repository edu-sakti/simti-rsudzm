<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'reset_token')) {
                $table->string('reset_token', 100)->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'reset_token_expires')) {
                $table->timestamp('reset_token_expires')->nullable()->after('reset_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'reset_token_expires')) {
                $table->dropColumn('reset_token_expires');
            }
            if (Schema::hasColumn('users', 'reset_token')) {
                $table->dropColumn('reset_token');
            }
        });
    }
};
