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
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            // Add nullable foreign keys for direct mapping
            $table->unsignedBigInteger('rapidapi_card_id')->nullable()->after('product_id')->index();
            $table->unsignedBigInteger('cardmarket_product_id')->nullable()->after('rapidapi_card_id')->index();
            
            // Foreign key constraints (optional, for data integrity)
            $table->foreign('rapidapi_card_id')
                ->references('id')
                ->on('rapidapi_cards')
                ->onDelete('set null');
            
            $table->foreign('cardmarket_product_id')
                ->references('cardmarket_product_id')
                ->on('cardmarket_products')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->dropForeign(['rapidapi_card_id']);
            $table->dropForeign(['cardmarket_product_id']);
            $table->dropIndex(['rapidapi_card_id']);
            $table->dropIndex(['cardmarket_product_id']);
            $table->dropColumn(['rapidapi_card_id', 'cardmarket_product_id']);
        });
    }
};
