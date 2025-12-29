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
        Schema::table('rapidapi_cards', function (Blueprint $table) {
            $table->text('cardmarket_url')->nullable()->after('tcggo_url')->comment('Direct Cardmarket product URL');
        });

        // Update existing records with direct Cardmarket URLs
        DB::statement("
            UPDATE rapidapi_cards 
            SET cardmarket_url = CONCAT('https://www.cardmarket.com/en/Pokemon/Products/Singles/', cardmarket_id)
            WHERE cardmarket_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapidapi_cards', function (Blueprint $table) {
            $table->dropColumn('cardmarket_url');
        });
    }
};
