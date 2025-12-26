<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add game_id to tcgcsv_groups
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            $table->foreignId('game_id')->nullable()->after('category_id')->constrained('games')->nullOnDelete();
            $table->index('game_id');
        });

        // Add game_id to tcgcsv_products
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->foreignId('game_id')->nullable()->after('category_id')->constrained('games')->nullOnDelete();
            $table->index('game_id');
        });

        // Add game_id to tcgcsv_prices
        Schema::table('tcgcsv_prices', function (Blueprint $table) {
            $table->foreignId('game_id')->nullable()->after('category_id')->constrained('games')->nullOnDelete();
            $table->index('game_id');
        });

        // Backfill: map category_id to game_id
        // Pokemon: category_id=3 -> game_id=1
        $pokemonGameId = DB::table('games')->where('tcgcsv_category_id', 3)->value('id');
        if ($pokemonGameId) {
            DB::table('tcgcsv_groups')->where('category_id', 3)->update(['game_id' => $pokemonGameId]);
            DB::table('tcgcsv_products')->where('category_id', 3)->update(['game_id' => $pokemonGameId]);
            DB::table('tcgcsv_prices')->where('category_id', 3)->update(['game_id' => $pokemonGameId]);
        }

        // MTG: category_id=1 -> game_id=2
        $mtgGameId = DB::table('games')->where('tcgcsv_category_id', 1)->value('id');
        if ($mtgGameId) {
            DB::table('tcgcsv_groups')->where('category_id', 1)->update(['game_id' => $mtgGameId]);
            DB::table('tcgcsv_products')->where('category_id', 1)->update(['game_id' => $mtgGameId]);
            DB::table('tcgcsv_prices')->where('category_id', 1)->update(['game_id' => $mtgGameId]);
        }

        // Yu-Gi-Oh: category_id=2 -> game_id=3
        $yugiohGameId = DB::table('games')->where('tcgcsv_category_id', 2)->value('id');
        if ($yugiohGameId) {
            DB::table('tcgcsv_groups')->where('category_id', 2)->update(['game_id' => $yugiohGameId]);
            DB::table('tcgcsv_products')->where('category_id', 2)->update(['game_id' => $yugiohGameId]);
            DB::table('tcgcsv_prices')->where('category_id', 2)->update(['game_id' => $yugiohGameId]);
        }
    }

    public function down(): void
    {
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropIndex(['game_id']);
            $table->dropColumn('game_id');
        });

        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropIndex(['game_id']);
            $table->dropColumn('game_id');
        });

        Schema::table('tcgcsv_prices', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropIndex(['game_id']);
            $table->dropColumn('game_id');
        });
    }
};
