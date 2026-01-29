<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tcgdx_sets', function (Blueprint $table) {
            $table->foreignId('game_id')->nullable()->after('tcgdex_id')->constrained('games')->onDelete('cascade');
            $table->index('game_id');
        });
        
        // Set default game_id to Pokemon (game_id = 1)
        DB::table('tcgdx_sets')->whereNull('game_id')->update(['game_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgdx_sets', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropIndex(['game_id']);
            $table->dropColumn('game_id');
        });
    }
};
