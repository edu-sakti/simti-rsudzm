<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('helpdesk_tickets', 'device_id')) {
                $table->foreignId('device_id')
                    ->nullable()
                    ->after('room_id')
                    ->constrained('devices')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        });

        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('helpdesk_tickets', 'sub_kategori')) {
                $table->dropColumn('sub_kategori');
            }
        });
    }

    public function down(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('helpdesk_tickets', 'device_id')) {
                $table->dropForeign(['device_id']);
                $table->dropColumn('device_id');
            }
        });

        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('helpdesk_tickets', 'sub_kategori')) {
                $table->string('sub_kategori')->nullable()->after('kategori');
            }
        });
    }
};
