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
        Schema::table('games', function (Blueprint $table) {
            $table->string('catalog_backend', 20)->default('tcgcsv')->after('code');
        });
        
        // Set Pokemon to use TCGDEX
        DB::table('games')
            ->where('code', 'pokemon')
            ->update(['catalog_backend' => 'tcgdex']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('catalog_backend');
        });
    }
};
