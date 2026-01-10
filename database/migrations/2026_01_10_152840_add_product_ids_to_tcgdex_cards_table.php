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
            $table->unsignedBigInteger('tcgplayer_product_id')->nullable()->after('tcgdex_id')->index();
            $table->unsignedBigInteger('cardmarket_product_id')->nullable()->after('tcgplayer_product_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgdx_cards', function (Blueprint $table) {
            $table->dropIndex(['tcgplayer_product_id']);
            $table->dropIndex(['cardmarket_product_id']);
            $table->dropColumn(['tcgplayer_product_id', 'cardmarket_product_id']);
        });
    }
};
