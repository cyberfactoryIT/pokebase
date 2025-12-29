<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table ensures idempotency - tracks individual evaluation runs
     * to prevent double-counting cards when re-running evaluations.
     */
    public function up(): void
    {
        Schema::create('deck_evaluation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('deck_evaluation_sessions')->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained('deck_evaluation_purchases')->onDelete('set null');
            $table->string('run_hash')->unique(); // Hash of cards to detect duplicates
            $table->integer('cards_count');
            $table->timestamp('evaluated_at');
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'run_hash']);
            $table->index('purchase_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deck_evaluation_runs');
    }
};
