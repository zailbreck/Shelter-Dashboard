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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('pid');
            $table->enum('status', ['running', 'stopped'])->default('running');
            $table->decimal('cpu_percent', 5, 2)->default(0);
            $table->decimal('memory_percent', 5, 2)->default(0);
            $table->integer('memory_mb')->default(0);
            $table->decimal('disk_read_mb', 10, 2)->default(0);
            $table->decimal('disk_write_mb', 10, 2)->default(0);
            $table->string('user', 100);
            $table->text('command')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            // Indexes for quick lookups
            $table->index(['agent_id', 'status']);
            $table->index(['agent_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
