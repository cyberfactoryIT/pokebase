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
            $table->text('hd_image_url')->nullable()->after('image_url')->comment('High resolution image from RapidAPI');
            $table->string('image_source', 50)->nullable()->after('hd_image_url')->comment('Source: tcgcsv, rapidapi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->dropColumn(['hd_image_url', 'image_source']);
        });
    }
};
