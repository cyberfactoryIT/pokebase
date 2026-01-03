<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapidapi_prices', function (Blueprint $table) {
            $table->id();
            $table->string('card_id')->unique();
            $table->unsignedInteger('episode_id')->index();
            $table->string('game', 50)->index();
            
            // Card info
            $table->string('name')->nullable();
            $table->string('number')->nullable();
            $table->string('rarity')->nullable();
            $table->string('image_url')->nullable();
            
            // Cardmarket prices (current)
            $table->decimal('cardmarket_avg', 10, 2)->nullable();
            $table->decimal('cardmarket_low', 10, 2)->nullable();
            $table->decimal('cardmarket_high', 10, 2)->nullable();
            $table->decimal('cardmarket_trend', 10, 2)->nullable();
            
            // TCGPlayer prices (current)
            $table->decimal('tcgplayer_market', 10, 2)->nullable();
            $table->decimal('tcgplayer_low', 10, 2)->nullable();
            $table->decimal('tcgplayer_high', 10, 2)->nullable();
            $table->decimal('tcgplayer_mid', 10, 2)->nullable();
            
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->index(['episode_id', 'game']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapidapi_prices');
    }
};
