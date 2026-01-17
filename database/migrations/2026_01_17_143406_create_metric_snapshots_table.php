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
        Schema::create('metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->enum('metric_type', ['cpu', 'memory', 'disk', 'network', 'io']);
            $table->decimal('avg_value', 10, 2);
            $table->decimal('min_value', 10, 2);
            $table->decimal('max_value', 10, 2);
            $table->decimal('low_value', 10, 2); // 25th percentile
            $table->decimal('high_value', 10, 2); // 75th percentile
            $table->enum('snapshot_period', ['1min', '5min', '1hour', '1day']);
            $table->timestamp('snapshot_time');
            $table->timestamp('created_at')->useCurrent();

            // Composite unique to prevent duplicates
            $table->unique(['agent_id', 'metric_type', 'snapshot_period', 'snapshot_time'], 'snapshot_unique');
            $table->index(['agent_id', 'metric_type', 'snapshot_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_snapshots');
    }
};
