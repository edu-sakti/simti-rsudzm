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
        Schema::create('ip_addrs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique(); // supports IPv4/IPv6
            $table->string('subnet', 45)->nullable();
            $table->enum('status', ['available', 'used'])->default('available');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_addrs');
    }
};
