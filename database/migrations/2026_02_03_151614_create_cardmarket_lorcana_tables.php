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
        // Products table for Lorcana (from CardMarket S3)
        Schema::create('cardmarket_products_lorcana', function (Blueprint $table) {
            $table->id();
            
            // Core Cardmarket identifiers (from JSON)
            $table->unsignedBigInteger('cardmarket_product_id')->unique();
            $table->unsignedBigInteger('id_category')->index(); // 1629 for Lorcana
            $table->string('category_name')->index();
            $table->unsignedBigInteger('id_expansion')->nullable()->index();
            $table->unsignedBigInteger('id_metacard')->nullable()->index();
            
            // Product information
            $table->string('name')->index();
            $table->date('date_added')->nullable();
            
            // Link to cmapi_cards (via cardmarket_id)
            $table->unsignedBigInteger('cmapi_card_id')->nullable()->index();
            
            // Store complete original JSON for reference
            $table->json('raw')->nullable();
            
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['id_category', 'id_expansion']);
            $table->index(['id_category', 'name']);
            
            // Foreign key to cmapi_cards
            $table->foreign('cmapi_card_id')
                ->references('id')
                ->on('cmapi_cards')
                ->onDelete('set null');
        });

        // Price quotes table for Lorcana (historical pricing snapshots)
        Schema::create('cardmarket_price_quotes_lorcana', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cardmarket_product_id')->index();
            $table->unsignedBigInteger('id_category')->index();
            $table->date('as_of_date')->index(); // Pricing snapshot date
            $table->char('currency', 3)->default('EUR');
            
            // Regular (non-foil) prices
            $table->decimal('avg', 10, 2)->nullable();
            $table->decimal('low', 10, 2)->nullable();
            $table->decimal('trend', 10, 2)->nullable();
            
            // Foil/Holo prices
            $table->decimal('avg_holo', 10, 2)->nullable();
            $table->decimal('low_holo', 10, 2)->nullable();
            $table->decimal('trend_holo', 10, 2)->nullable();
            
            // Trend prices (1-day, 7-day, 30-day averages)
            $table->decimal('avg1', 10, 2)->nullable();
            $table->decimal('avg7', 10, 2)->nullable();
            $table->decimal('avg30', 10, 2)->nullable();
            
            // Store complete original JSON for reference
            $table->json('raw')->nullable();
            
            $table->timestamps();

            // Ensure one price quote per product per date (historical snapshots)
            $table->unique(['cardmarket_product_id', 'as_of_date'], 'cm_lorcana_prices_prod_date_uniq');

            // Foreign key to products table
            $table->foreign('cardmarket_product_id')
                ->references('cardmarket_product_id')
                ->on('cardmarket_products_lorcana')
                ->onDelete('cascade');
        });

        // Import runs tracking for Lorcana
        Schema::create('cardmarket_import_runs_lorcana', function (Blueprint $table) {
            $table->id();
            $table->uuid('run_uuid')->unique();
            $table->string('type')->default('full'); // full, products, prices
            $table->string('status')->default('pending'); // pending, running, completed, failed
            $table->integer('rows_read')->default(0);
            $table->integer('rows_upserted')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardmarket_price_quotes_lorcana');
        Schema::dropIfExists('cardmarket_products_lorcana');
        Schema::dropIfExists('cardmarket_import_runs_lorcana');
    }
};
