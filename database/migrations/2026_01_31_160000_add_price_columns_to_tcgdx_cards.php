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
        Schema::table('tcgdx_cards', function (Blueprint $table) {
            $table->decimal('price_usd', 10, 2)->nullable()->after('rarity');
            $table->decimal('price_eur', 10, 2)->nullable()->after('price_usd');
            
            // Add indexes for price queries
            $table->index('price_usd');
            $table->index('price_eur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgdx_cards', function (Blueprint $table) {
            $table->dropIndex(['price_usd']);
            $table->dropIndex(['price_eur']);
            $table->dropColumn(['price_usd', 'price_eur']);
        });
    }
};
