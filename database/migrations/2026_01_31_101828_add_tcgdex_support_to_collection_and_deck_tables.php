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
        // Add TCGDEX support to user_collection table
        Schema::table('user_collection', function (Blueprint $table) {
            // Add tcgdex_card_id column
            $table->unsignedBigInteger('tcgdex_card_id')->nullable()->after('product_id');
            
            // Add foreign key to tcgdx_cards
            $table->foreign('tcgdex_card_id')
                ->references('id')
                ->on('tcgdx_cards')
                ->onDelete('cascade');
            
            // Make product_id nullable since we can use either backend
            $table->unsignedBigInteger('product_id')->nullable()->change();
            
            // Add composite indexes for efficient queries on both backends
            $table->index(['user_id', 'tcgdex_card_id']);
        });
        
        // Add TCGDEX support to deck_cards table
        Schema::table('deck_cards', function (Blueprint $table) {
            // Add tcgdex_card_id column
            $table->unsignedBigInteger('tcgdex_card_id')->nullable()->after('product_id');
            
            // Add foreign key to tcgdx_cards
            $table->foreign('tcgdex_card_id')
                ->references('id')
                ->on('tcgdx_cards')
                ->onDelete('cascade');
            
            // Make product_id nullable since we can use either backend
            $table->unsignedBigInteger('product_id')->nullable()->change();
            
            // Add composite indexes for efficient queries on both backends
            $table->index(['deck_id', 'tcgdex_card_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_collection', function (Blueprint $table) {
            $table->dropForeign(['tcgdex_card_id']);
            $table->dropIndex(['user_id', 'tcgdex_card_id']);
            $table->dropColumn('tcgdex_card_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
        
        Schema::table('deck_cards', function (Blueprint $table) {
            $table->dropForeign(['tcgdex_card_id']);
            $table->dropIndex(['deck_id', 'tcgdex_card_id']);
            $table->dropColumn('tcgdex_card_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
