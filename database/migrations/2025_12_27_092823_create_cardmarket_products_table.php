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
        Schema::create('cardmarket_products', function (Blueprint $table) {
            $table->id();
            
            // Core Cardmarket identifiers (from JSON)
            $table->unsignedBigInteger('cardmarket_product_id')->unique();
            $table->unsignedBigInteger('id_category')->index();
            $table->string('category_name')->index();
            $table->unsignedBigInteger('id_expansion')->nullable()->index();
            $table->unsignedBigInteger('id_metacard')->nullable()->index();
            
            // Product information
            $table->string('name')->index();
            $table->date('date_added')->nullable();
            
            // Store complete original JSON for reference
            $table->json('raw')->nullable();
            
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['id_category', 'id_expansion']);
            $table->index(['id_category', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cardmarket_products');
    }
};
