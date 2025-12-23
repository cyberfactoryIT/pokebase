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
        Schema::create('user_collection', function (Blueprint $table) {
            $table->id();
            
            // Owner of the card
            $table->unsignedBigInteger('user_id');
            
            // Card reference (using tcgcsv_products for now)
            $table->unsignedBigInteger('product_id');
            
            // Collection details
            $table->integer('quantity')->default(1);
            $table->string('condition')->nullable(); // mint, near_mint, played, etc.
            $table->boolean('is_foil')->default(false);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
                
            $table->foreign('product_id')
                ->references('product_id')
                ->on('tcgcsv_products')
                ->onDelete('cascade');
            
            // Prevent duplicates: same card with same condition/foil
            $table->unique(['user_id', 'product_id', 'condition', 'is_foil']);
            
            // Indexes for quick lookups
            $table->index(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_collection');
    }
};
