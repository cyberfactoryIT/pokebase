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
        // Check if cardmarket_price_quotes_lorcana exists and add missing constraints
        if (Schema::hasTable('cardmarket_price_quotes_lorcana')) {
            Schema::table('cardmarket_price_quotes_lorcana', function (Blueprint $table) {
                // Check if unique constraint doesn't exist
                $indexes = DB::select("SHOW INDEXES FROM cardmarket_price_quotes_lorcana WHERE Key_name = 'cm_lorcana_prices_prod_date_uniq'");
                
                if (empty($indexes)) {
                    $table->unique(['cardmarket_product_id', 'as_of_date'], 'cm_lorcana_prices_prod_date_uniq');
                }
                
                // Check if foreign key doesn't exist
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'cardmarket_price_quotes_lorcana'
                    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND CONSTRAINT_NAME LIKE '%cardmarket_product_id%'
                ");
                
                if (empty($foreignKeys)) {
                    $table->foreign('cardmarket_product_id')
                        ->references('cardmarket_product_id')
                        ->on('cardmarket_products_lorcana')
                        ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cardmarket_price_quotes_lorcana')) {
            Schema::table('cardmarket_price_quotes_lorcana', function (Blueprint $table) {
                $table->dropUnique('cm_lorcana_prices_prod_date_uniq');
                $table->dropForeign(['cardmarket_product_id']);
            });
        }
    }
};
