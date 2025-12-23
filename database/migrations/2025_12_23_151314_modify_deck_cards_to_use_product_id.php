<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all foreign keys referencing card_catalog_id and drop them
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'deck_cards' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE deck_cards DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                // Continue if already dropped
            }
        }
        
        // Now drop indexes
        try {
            DB::statement('ALTER TABLE deck_cards DROP INDEX deck_cards_card_catalog_id_foreign');
        } catch (\Exception $e) {
            // Continue
        }
        
        try {
            DB::statement('ALTER TABLE deck_cards DROP INDEX deck_cards_deck_id_card_catalog_id_unique');
        } catch (\Exception $e) {
            // Continue
        }
        
        // Drop column and add new structure
        Schema::table('deck_cards', function (Blueprint $table) {
            $table->dropColumn('card_catalog_id');
            $table->unsignedBigInteger('product_id')->after('deck_id');
            
            // Add foreign key to tcgcsv_products
            $table->foreign('product_id')
                ->references('product_id')
                ->on('tcgcsv_products')
                ->onDelete('cascade');
            
            // Add unique constraint
            $table->unique(['deck_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deck_cards', function (Blueprint $table) {
            // Drop new foreign key and column
            $table->dropForeign(['product_id']);
            $table->dropUnique(['deck_id', 'product_id']);
            $table->dropColumn('product_id');
            
            // Restore old card_catalog_id
            $table->unsignedBigInteger('card_catalog_id')->after('deck_id');
            
            $table->foreign('card_catalog_id')
                ->references('id')
                ->on('card_catalog')
                ->onDelete('cascade');
            
            $table->unique(['deck_id', 'card_catalog_id']);
        });
    }
};
