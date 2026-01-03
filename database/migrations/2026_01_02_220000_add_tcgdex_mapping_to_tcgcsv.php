<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add TCGdex set mapping to groups
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            $table->string('tcgdex_set_id', 50)->nullable()->after('rapidapi_episode_id');
            $table->index('tcgdex_set_id');
        });

        // Add TCGdex card mapping to products
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->string('tcgdex_card_id', 100)->nullable()->after('card_number');
            $table->index('tcgdex_card_id');
        });
    }

    public function down(): void
    {
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            $table->dropIndex(['tcgdex_set_id']);
            $table->dropColumn('tcgdex_set_id');
        });

        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->dropIndex(['tcgdex_card_id']);
            $table->dropColumn('tcgdex_card_id');
        });
    }
};
