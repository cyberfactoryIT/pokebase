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
        // Staging area for CardMarket products (before validation)
        Schema::create('staging_cmapi_products', function (Blueprint $table) {
            $table->id();
            $table->string('cardmarket_id')->unique();
            $table->string('game', 50); // lorcana, onepiece
            $table->string('name');
            $table->string('set_name')->nullable();
            $table->string('number')->nullable();
            $table->string('rarity')->nullable();
            $table->string('language', 10)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->string('status', 20)->default('pending'); // pending, validated, error
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['game', 'status']);
            $table->index('fetched_at');
        });

        // Staging area for CardMarket prices
        Schema::create('staging_cmapi_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staging_product_id')->constrained('staging_cmapi_products')->onDelete('cascade');
            $table->string('cardmarket_id');
            $table->string('language', 10)->nullable();
            $table->string('condition', 20)->nullable(); // NM, LP, MP, HP, DMG
            $table->decimal('price_eur', 10, 2)->nullable();
            $table->decimal('price_trend_eur', 10, 2)->nullable(); // 30d average
            $table->integer('available_items')->nullable();
            $table->timestamp('price_date')->useCurrent();
            $table->timestamps();
            
            $table->index(['cardmarket_id', 'language', 'condition']);
        });

        // Production price history (validated data)
        Schema::create('cmapi_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cmapi_card_id')->constrained('cmapi_cards')->onDelete('cascade');
            $table->string('cardmarket_id');
            $table->string('language', 10)->nullable();
            $table->string('condition', 20)->default('NM');
            $table->decimal('price_eur', 10, 2);
            $table->decimal('price_trend_eur', 10, 2)->nullable();
            $table->integer('available_items')->nullable();
            $table->date('price_date')->index();
            $table->timestamps();
            
            $table->index(['cmapi_card_id', 'price_date'], 'idx_card_date');
            $table->index(['cmapi_card_id', 'language', 'price_date'], 'idx_card_lang_date');
            $table->unique(['cmapi_card_id', 'language', 'condition', 'price_date'], 'uniq_card_lang_cond_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cmapi_price_history');
        Schema::dropIfExists('staging_cmapi_prices');
        Schema::dropIfExists('staging_cmapi_products');
    }
};
