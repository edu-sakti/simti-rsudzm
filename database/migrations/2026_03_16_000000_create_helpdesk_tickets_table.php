<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('no_ticket')->unique();
            $table->date('tanggal');
            $table->string('pelapor');
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('kategori');
            $table->string('sub_kategori')->nullable();
            $table->text('kendala');
            $table->string('prioritas');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('status');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_tickets');
    }
};
