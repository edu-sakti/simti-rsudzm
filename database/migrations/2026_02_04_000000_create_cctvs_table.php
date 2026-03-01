<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cctvs', function (Blueprint $table) {
            $table->id();
            $table->string('room_id');
            $table->string('status', 20);
            $table->text('keterangan')->nullable();
            $table->string('ip', 45);
            $table->timestamps();

            $table->foreign('room_id')->references('room_id')->on('rooms')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cctvs');
    }
};
