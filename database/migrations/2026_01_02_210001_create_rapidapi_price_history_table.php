<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapidapi_price_history', function (Blueprint $table) {
            $table->id();
            $table->string('card_id')->index();
            $table->unsignedInteger('episode_id')->index();
            $table->string('game', 50)->index();
            $table->date('snapshot_date')->index();
            
            // Cardmarket prices
            $table->decimal('cardmarket_avg', 10, 2)->nullable();
            $table->decimal('cardmarket_low', 10, 2)->nullable();
            $table->decimal('cardmarket_high', 10, 2)->nullable();
            $table->decimal('cardmarket_trend', 10, 2)->nullable();
            
            // TCGPlayer prices
            $table->decimal('tcgplayer_market', 10, 2)->nullable();
            $table->decimal('tcgplayer_low', 10, 2)->nullable();
            $table->decimal('tcgplayer_high', 10, 2)->nullable();
            $table->decimal('tcgplayer_mid', 10, 2)->nullable();
            
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            // Unique constraint: one snapshot per card per day
            $table->unique(['card_id', 'snapshot_date'], 'card_snapshot_unique');
            $table->index(['episode_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapidapi_price_history');
    }
};
