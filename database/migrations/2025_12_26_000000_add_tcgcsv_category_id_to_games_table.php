<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->integer('tcgcsv_category_id')->nullable()->after('code');
            $table->unique('tcgcsv_category_id');
        });

        // Update Pokemon game with category_id 3
        DB::table('games')
            ->where('code', 'pokemon')
            ->update(['tcgcsv_category_id' => 3]);
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropUnique(['tcgcsv_category_id']);
            $table->dropColumn('tcgcsv_category_id');
        });
    }
};
