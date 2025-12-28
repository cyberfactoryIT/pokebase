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
        Schema::create('deck_valuation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deck_valuation_id')->constrained('deck_valuations')->onDelete('cascade');
            $table->integer('tcgcsv_product_id');
            $table->integer('qty')->default(1);
            $table->timestamps();
            
            $table->index('deck_valuation_id');
            $table->index('tcgcsv_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deck_valuation_items');
    }
};
