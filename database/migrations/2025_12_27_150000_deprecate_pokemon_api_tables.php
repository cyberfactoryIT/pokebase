<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration deprecates the Pokemon TCG API tables (card_catalog and pokemon_sets)
     * which have been replaced by the TCGCSV system (tcgcsv_products and tcgcsv_groups).
     * 
     * Reasons for deprecation:
     * - card_catalog: only 263 cards vs tcgcsv_products with 30,757 cards
     * - Pokemon TCG API: unstable with frequent 504 timeouts
     * - user_collection already uses tcgcsv_products.product_id
     * - No foreign keys depend on these tables
     */
    public function up(): void
    {
        // Rename tables to mark as deprecated instead of dropping immediately
        // This allows for rollback if needed
        Schema::rename('card_catalog', 'deprecated_card_catalog');
        Schema::rename('pokemon_sets', 'deprecated_pokemon_sets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('deprecated_card_catalog', 'card_catalog');
        Schema::rename('deprecated_pokemon_sets', 'pokemon_sets');
    }
};
