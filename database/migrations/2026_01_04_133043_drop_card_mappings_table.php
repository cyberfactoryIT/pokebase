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
        Schema::dropIfExists('card_mappings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('card_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rapidapi_card_id')->index();
            $table->unsignedBigInteger('cardmarket_product_id')->nullable()->index();
            $table->unsignedInteger('tcgcsv_product_id')->nullable()->index();
            $table->string('game', 50)->index();
            $table->string('match_method', 50);
            $table->decimal('confidence', 5, 2)->default(0);
            $table->string('card_name')->nullable();
            $table->string('card_number', 50)->nullable();
            $table->string('expansion_name')->nullable();
            $table->timestamp('mapped_at')->nullable();
            $table->timestamps();
            
            $table->unique('rapidapi_card_id');
        });
    }
};
