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
            // Add denormalized price fields for better performance
            $table->decimal('price_eur', 10, 2)->nullable()->after('raw');
            $table->decimal('price_usd', 10, 2)->nullable()->after('price_eur');
            
            // Add indexes for price queries
            $table->index('price_eur');
            $table->index('price_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgdx_cards', function (Blueprint $table) {
            $table->dropIndex(['price_eur']);
            $table->dropIndex(['price_usd']);
            $table->dropColumn(['price_eur', 'price_usd']);
        });
    }
};
