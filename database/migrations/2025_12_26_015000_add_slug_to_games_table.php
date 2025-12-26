<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('games', 'slug')) {
            Schema::table('games', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('code');
            });
        }

        // Generate slugs from existing codes
        DB::table('games')->whereNull('slug')->get()->each(function ($game) {
            DB::table('games')
                ->where('id', $game->id)
                ->update(['slug' => $game->code]);
        });

        // Now add unique constraint if not exists
        $indexes = DB::select("SHOW INDEX FROM games WHERE Key_name = 'games_slug_unique'");
        if (empty($indexes)) {
            Schema::table('games', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
