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
        Schema::create('pipeline_runs', function (Blueprint $table) {
            $table->id();
            $table->string('task_name', 100)->index(); // cardmarket:etl, tcgcsv:import, etc.
            $table->enum('status', ['running', 'success', 'failed'])->default('running')->index();
            $table->timestamp('started_at')->index();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable()->storedAs('TIMESTAMPDIFF(SECOND, started_at, finished_at)');
            
            // Statistics
            $table->unsignedBigInteger('rows_processed')->nullable();
            $table->unsignedBigInteger('rows_created')->nullable();
            $table->unsignedBigInteger('rows_updated')->nullable();
            $table->unsignedBigInteger('rows_deleted')->nullable();
            $table->unsignedInteger('errors_count')->default(0);
            
            // Error details
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            
            // Additional metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['task_name', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_runs');
    }
};
