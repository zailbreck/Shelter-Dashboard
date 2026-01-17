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
        Schema::table('agents', function (Blueprint $table) {
            // Add HWID and agent_id columns
            $table->string('hwid', 16)->after('id')->nullable();
            $table->string('agent_id')->after('hwid')->unique()->nullable();

            // Make hostname nullable since we'll use agent_id as primary identifier
            $table->string('hostname')->nullable()->change();

            // Soft deletes
            $table->softDeletes();

            // Indexes
            $table->index('hwid');
            $table->index('agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['hwid', 'agent_id']);
        });
    }
};
