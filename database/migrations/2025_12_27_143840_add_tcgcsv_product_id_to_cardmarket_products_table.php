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
        Schema::table('cardmarket_products', function (Blueprint $table) {
            $table->unsignedBigInteger('tcgcsv_product_id')->nullable()->after('id_metacard');
            $table->index('tcgcsv_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cardmarket_products', function (Blueprint $table) {
            $table->dropIndex(['tcgcsv_product_id']);
            $table->dropColumn('tcgcsv_product_id');
        });
    }
};
