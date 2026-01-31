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
            $table->decimal('price_usd', 10, 2)->nullable()->after('cardmarket_price_eur');
            $table->index('price_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->dropIndex(['price_usd']);
            $table->dropColumn('price_usd');
        });
    }
};
