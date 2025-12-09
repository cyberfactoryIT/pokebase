<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use this migration only if you have a `user_cards` table.
        if (!Schema::hasTable('user_cards')) {
            return;
        }

        Schema::table('user_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id')->nullable()->after('user_id');

            $table->foreign('game_id')
                ->references('id')
                ->on('games');
        });

        $pokemonGameId = DB::table('games')->where('code', 'pokemon')->value('id');

        if ($pokemonGameId) {
            DB::table('user_cards')->update([
                'game_id' => $pokemonGameId,
            ]);
        }

        Schema::table('user_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_cards')) {
            return;
        }

        Schema::table('user_cards', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropColumn('game_id');
        });
    }
};
