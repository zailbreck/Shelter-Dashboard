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
        Schema::create('metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('agent_id');
            $table->enum('metric_type', ['cpu', 'memory', 'disk', 'network', 'io']);
            $table->decimal('value', 10, 2);
            $table->string('unit', 20); // %, MB, GB, Mbps, etc.
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            // Indexes for time-series queries
            $table->index(['agent_id', 'metric_type', 'recorded_at']);
            $table->index('recorded_at'); // For cleanup/archival queries
        });

        // Note: For production, consider partitioning by recorded_at
        // This can be done via raw SQL after initial migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};
