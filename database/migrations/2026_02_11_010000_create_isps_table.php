<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('isps', function (Blueprint $table) {
            $table->id();
            $table->string('nama_isp');
            $table->enum('jenis_koneksi', ['fiber', 'radio']);
            $table->string('bandwidth');
            $table->string('ip_address');
            $table->unsignedBigInteger('ruang_installasi');
            $table->string('pic_isp');
            $table->string('no_telepon');
            $table->enum('status', ['aktif', 'backup']);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('ruang_installasi')
                ->references('id')
                ->on('rooms')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isps');
    }
};
