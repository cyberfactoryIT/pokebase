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
            $table->integer('hp')->nullable()->after('cardmarket_url');
            $table->string('artist_name', 255)->nullable()->after('hp');
            $table->string('supertype', 50)->nullable()->after('artist_name');
            $table->string('rapidapi_rarity', 100)->nullable()->after('supertype');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->dropColumn(['hp', 'artist_name', 'supertype', 'rapidapi_rarity']);
        });
    }
};
