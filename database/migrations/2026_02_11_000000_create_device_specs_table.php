<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')
                  ->constrained('devices')
                  ->onDelete('cascade');
            $table->string('processor')->nullable();
            $table->string('ram')->nullable();
            $table->enum('storage_type', ['HDD', 'SSD'])->nullable();
            $table->string('storage_capacity')->nullable();
            $table->string('gpu')->nullable();
            $table->string('os')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_specs');
    }
};
