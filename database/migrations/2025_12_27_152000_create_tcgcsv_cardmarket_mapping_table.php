<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates a pivot table to map TCGCSV products to Cardmarket metacards.
     * This allows one-to-many relationship: 1 TCGCSV product → N Cardmarket variants
     * 
     * Example:
     * TCGCSV: "Pikachu #25" → Cardmarket idMetacard: 211614 → 23 product variants
     */
    public function up(): void
    {
        Schema::create('tcgcsv_cardmarket_mapping', function (Blueprint $table) {
            $table->id();
            
            // TCGCSV side
            $table->unsignedBigInteger('tcgcsv_product_id');
            
            // Cardmarket side (metacard groups variants)
            $table->unsignedBigInteger('cardmarket_metacard_id');
            
            // Matching metadata
            $table->decimal('confidence_score', 5, 2)->nullable()->comment('Matching confidence 0-100');
            $table->string('match_method')->nullable()->comment('auto, manual, fuzzy, exact');
            $table->text('match_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('tcgcsv_product_id');
            $table->index('cardmarket_metacard_id');
            
            // Unique constraint - one TCGCSV product can only map to one metacard
            $table->unique('tcgcsv_product_id', 'unique_tcgcsv_mapping');
            
            // Foreign key to TCGCSV (if exists)
            $table->foreign('tcgcsv_product_id')
                  ->references('id')
                  ->on('tcgcsv_products')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcgcsv_cardmarket_mapping');
    }
};
