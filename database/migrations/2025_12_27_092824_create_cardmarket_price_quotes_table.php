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
        Schema::create('cardmarket_price_quotes', function (Blueprint $table) {
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
            $table->unique(['cardmarket_product_id', 'as_of_date']);

            // Foreign key to products table
            $table->foreign('cardmarket_product_id')
                ->references('cardmarket_product_id')
                ->on('cardmarket_products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardmarket_price_quotes');
    }
};
