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
        Schema::table('card_mappings', function (Blueprint $table) {
            $table->unsignedBigInteger('tcgdx_card_id')->nullable()->after('tcgcsv_product_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_mappings', function (Blueprint $table) {
            $table->dropColumn('tcgdx_card_id');
        });
    }
};
