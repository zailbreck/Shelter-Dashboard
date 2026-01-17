<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('hostname')->unique();
            $table->string('ip_address');
            $table->string('api_token', 64)->unique();
            $table->string('os_type', 50);
            $table->string('os_version', 100);
            $table->integer('cpu_cores');
            $table->bigInteger('total_memory'); // in bytes
            $table->bigInteger('total_disk'); // in bytes
            $table->enum('status', ['online', 'offline', 'error'])->default('offline');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('status');
            $table->index('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
