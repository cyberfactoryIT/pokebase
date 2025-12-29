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
        Schema::create('rapidapi_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rapidapi_id')->unique()->comment('ID from RapidAPI');
            $table->unsignedInteger('cardmarket_id')->nullable()->index()->comment('Cardmarket product ID');
            $table->string('game', 50)->index()->comment('Game: pokemon, mtg, yugioh');
            
            // Basic card info
            $table->string('name')->index();
            $table->string('name_numbered')->nullable();
            $table->string('slug')->index();
            $table->string('type', 50)->nullable()->comment('singles, sealed, etc');
            $table->string('card_number', 50)->nullable();
            $table->unsignedInteger('hp')->nullable();
            $table->string('rarity', 100)->nullable()->index();
            $table->string('supertype', 100)->nullable()->index();
            $table->string('tcgid', 100)->nullable();
            
            // Episode/Expansion info
            $table->json('episode')->nullable()->comment('Expansion details');
            $table->unsignedInteger('episode_id')->nullable()->index();
            $table->string('episode_name')->nullable();
            $table->string('episode_slug')->nullable()->index();
            $table->date('episode_released_at')->nullable();
            
            // Artist
            $table->json('artist')->nullable();
            
            // Prices
            $table->json('prices')->nullable()->comment('All price data from API');
            $table->decimal('price_eur', 10, 2)->nullable()->index()->comment('Main EUR price');
            
            // Links and images
            $table->text('image_url')->nullable();
            $table->text('tcggo_url')->nullable();
            $table->json('links')->nullable();
            
            // Metadata
            $table->json('raw_data')->nullable()->comment('Full API response');
            $table->timestamp('last_synced_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['game', 'episode_slug']);
            $table->index(['game', 'rarity']);
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapidapi_cards');
    }
};
