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
        Schema::create('rapidapi_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('game', 50)->index()->comment('Game: pokemon, mtg, yugioh');
            $table->enum('status', ['running', 'completed', 'failed'])->default('running')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            
            // Stats
            $table->unsignedInteger('pages_fetched')->default(0);
            $table->unsignedInteger('total_pages')->nullable();
            $table->unsignedInteger('cards_fetched')->default(0);
            $table->unsignedInteger('cards_inserted')->default(0);
            $table->unsignedInteger('cards_updated')->default(0);
            
            // Error tracking
            $table->text('error_message')->nullable();
            
            // Metadata
            $table->json('meta')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapidapi_sync_logs');
    }
};
