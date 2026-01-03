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
        // 1) staging_sets - Raw set/expansion data from all sources
        Schema::create('staging_sets', function (Blueprint $table) {
            $table->id();
            $table->string('source_code', 50)->comment('tcgcsv, tcgdex, pokemontcg_io, cardmarket, cardmarket_api');
            $table->string('external_set_id')->comment('Provider set/expansion ID (group_id, set.id, episode.id, etc.)');
            $table->string('set_code')->nullable()->comment('Short code like BS, XY, swsh3, base1');
            $table->string('name')->nullable();
            $table->string('series')->nullable();
            $table->date('release_date')->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('symbol_url', 500)->nullable();
            $table->json('payload_json')->comment('Unmodified raw API response');
            $table->char('payload_hash', 64)->comment('SHA-256 of payload_json for deduplication');
            $table->dateTime('observed_at')->comment('When this payload was captured');
            $table->timestamps();

            // Unique constraint: versioning without duplicate spam
            $table->unique(['source_code', 'external_set_id', 'payload_hash'], 'staging_sets_unique');
            
            // Performance indexes
            $table->index(['source_code', 'external_set_id'], 'staging_sets_source_external');
            $table->index('set_code');
            $table->index('release_date');
        });

        // 2) staging_products - Raw card/product data from all sources
        Schema::create('staging_products', function (Blueprint $table) {
            $table->id();
            $table->string('source_code', 50);
            $table->string('external_product_id')->comment('Provider product/card ID (productId, card.id, data.id, etc.)');
            $table->string('external_set_id')->nullable()->comment('References provider set ID (not FK)');
            $table->string('set_code')->nullable();
            $table->string('collector_number')->nullable()->comment('e.g., 001/102, GG69, TG01/TG30 - MUST be string');
            $table->string('name')->nullable();
            $table->string('supertype', 50)->nullable()->comment('Pokémon, Trainer, Energy');
            $table->string('rarity')->nullable();
            $table->string('finish')->nullable()->comment('normal, reverse, holofoil, 1st edition, etc.');
            $table->string('image_url', 500)->nullable();
            $table->json('payload_json')->comment('Unmodified raw API response');
            $table->char('payload_hash', 64)->comment('SHA-256 of payload_json');
            $table->dateTime('observed_at');
            $table->timestamps();

            // Unique constraint: versioning per product
            $table->unique(['source_code', 'external_product_id', 'payload_hash'], 'staging_products_unique');
            
            // Performance indexes
            $table->index(['source_code', 'external_product_id'], 'staging_products_source_external');
            $table->index(['source_code', 'external_set_id'], 'staging_products_source_set');
            $table->index(['set_code', 'collector_number'], 'staging_products_set_number');
            $table->index('name');
        });

        // 3) staging_prices - Time-series price data from all sources
        Schema::create('staging_prices', function (Blueprint $table) {
            $table->id();
            $table->string('source_code', 50);
            $table->string('external_product_id')->comment('Join key to staging_products');
            $table->string('finish')->nullable()->comment('normal, reverse, holofoil, etc.');
            $table->string('market_region', 10)->nullable()->comment('US, EU');
            $table->char('currency', 3)->nullable()->comment('USD, EUR, GBP');
            $table->string('condition', 50)->nullable()->comment('NM, LP, MP, etc.');
            $table->decimal('low_price', 10, 2)->nullable();
            $table->decimal('mid_price', 10, 2)->nullable();
            $table->decimal('high_price', 10, 2)->nullable();
            $table->decimal('market_price', 10, 2)->nullable();
            $table->decimal('avg_7d', 10, 2)->nullable();
            $table->decimal('avg_30d', 10, 2)->nullable();
            $table->decimal('direct_low_price', 10, 2)->nullable();
            $table->json('graded_json')->nullable()->comment('PSA, BGS, CGC graded prices');
            $table->boolean('anomaly_flag')->default(false)->comment('Is this price invalid/suspicious?');
            $table->string('anomaly_reason')->nullable();
            $table->json('payload_json')->comment('Unmodified raw price data');
            $table->char('payload_hash', 64);
            $table->dateTime('observed_at');
            $table->timestamps();

            // Unique constraint: time-series per product+finish+condition
            $table->unique(
                ['source_code', 'external_product_id', 'finish', 'currency', 'condition', 'observed_at'],
                'staging_prices_unique'
            );
            
            // Performance indexes
            $table->index(['source_code', 'external_product_id'], 'staging_prices_source_product');
            $table->index('observed_at');
            $table->index(['source_code', 'market_region', 'observed_at'], 'staging_prices_region_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staging_prices');
        Schema::dropIfExists('staging_products');
        Schema::dropIfExists('staging_sets');
    }
};
