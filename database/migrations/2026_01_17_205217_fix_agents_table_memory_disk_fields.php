<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            // Make existing fields nullable first
            $table->integer('cpu_cores')->nullable()->change();
            $table->string('api_token', 64)->nullable()->change();
            $table->bigInteger('total_memory')->nullable()->change();
            $table->bigInteger('total_disk')->nullable()->change();
        });

        // Rename columns
        Schema::table('agents', function (Blueprint $table) {
            $table->renameColumn('total_memory', 'total_memory_mb');
            $table->renameColumn('total_disk', 'total_disk_gb');
        });

        // Convert values from bytes to MB/GB for existing rows
        DB::statement('UPDATE agents SET total_memory_mb = ROUND(total_memory_mb / (1024 * 1024)) WHERE total_memory_mb IS NOT NULL');
        DB::statement('UPDATE agents SET total_disk_gb = ROUND(total_disk_gb / (1024 * 1024 * 1024), 2) WHERE total_disk_gb IS NOT NULL');

        // Change column types
        Schema::table('agents', function (Blueprint $table) {
            $table->integer('total_memory_mb')->nullable()->change();
            $table->decimal('total_disk_gb', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->renameColumn('total_memory_mb', 'total_memory');
            $table->renameColumn('total_disk_gb', 'total_disk');
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->bigInteger('total_memory')->change();
            $table->bigInteger('total_disk')->change();
            $table->integer('cpu_cores')->nullable(false)->change();
            $table->string('api_token', 64)->nullable(false)->change();
        });
    }
};
