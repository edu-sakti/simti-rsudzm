<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('role');
                $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'reset_token_expired_at')) {
                $table->timestamp('reset_token_expired_at')->nullable()->after('reset_token');
            }
        });

        if (Schema::hasColumn('users', 'reset_token_expires') && Schema::hasColumn('users', 'reset_token_expired_at')) {
            DB::table('users')
                ->whereNotNull('reset_token_expires')
                ->update(['reset_token_expired_at' => DB::raw('reset_token_expires')]);
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'reset_token_expires')) {
                $table->dropColumn('reset_token_expires');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
            if (!Schema::hasColumn('users', 'reset_token_expires')) {
                $table->timestamp('reset_token_expires')->nullable()->after('reset_token');
            }
            if (Schema::hasColumn('users', 'reset_token_expired_at')) {
                $table->dropColumn('reset_token_expired_at');
            }
        });
    }
};
