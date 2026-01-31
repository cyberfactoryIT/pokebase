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
        Schema::table('cmapi_cards', function (Blueprint $table) {
            $table->string('artist_name')->nullable()->after('color');
            $table->string('slug')->nullable()->after('cmapi_id');
            $table->string('tcggo_url')->nullable()->after('slug');
            $table->integer('cardmarket_id')->nullable()->after('cmapi_id');
            $table->integer('hp')->nullable()->after('lore_value'); // For some games
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cmapi_cards', function (Blueprint $table) {
            $table->dropColumn(['artist_name', 'slug', 'tcggo_url', 'cardmarket_id', 'hp']);
        });
    }
};
