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
        Schema::create('card_mappings', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->unsignedBigInteger('rapidapi_card_id')->nullable()->index();
            $table->unsignedBigInteger('cardmarket_product_id')->nullable()->index();
            $table->unsignedBigInteger('tcgcsv_product_id')->nullable()->index();
            
            // Mapping metadata
            $table->string('game', 50)->index();
            $table->enum('match_method', [
                'cardmarket_id',      // Matched via cardmarket_id
                'name_number',        // Matched via name + card number
                'name_expansion',     // Matched via name + expansion
                'manual',             // Manually mapped
            ])->index();
            $table->decimal('confidence', 3, 2)->default(1.00)->comment('Match confidence 0.00-1.00');
            
            // Additional info
            $table->string('card_name')->nullable();
            $table->string('card_number', 50)->nullable();
            $table->string('expansion_name')->nullable();
            
            // Metadata
            $table->json('meta')->nullable()->comment('Additional mapping info');
            $table->timestamp('mapped_at')->nullable();
            $table->timestamps();
            
            // Unique constraints
            $table->unique(['rapidapi_card_id', 'tcgcsv_product_id']);
            
            // Indexes
            $table->index(['game', 'match_method']);
            $table->index('confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_mappings');
    }
};
