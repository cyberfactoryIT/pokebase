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
        Schema::create('tcgcsv_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->string('status')->default('started'); // started, in_progress, completed, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Groups stats
            $table->integer('groups_processed')->default(0);
            $table->integer('groups_new')->default(0);
            $table->integer('groups_updated')->default(0);
            $table->integer('groups_failed')->default(0);
            
            // Products stats
            $table->integer('products_processed')->default(0);
            $table->integer('products_new')->default(0);
            $table->integer('products_updated')->default(0);
            $table->integer('products_failed')->default(0);
            
            // Prices stats
            $table->integer('prices_processed')->default(0);
            $table->integer('prices_new')->default(0);
            $table->integer('prices_updated')->default(0);
            $table->integer('prices_failed')->default(0);
            
            // Progress tracking
            $table->json('groups_completed')->nullable(); // Array of group_ids processed
            $table->json('error_details')->nullable(); // Array of errors by group_id
            $table->json('options')->nullable(); // Command options used
            
            $table->timestamps();
            
            $table->index('status');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcgcsv_import_logs');
    }
};
