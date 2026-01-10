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
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            // Add unique index on rapidapi_episode_id
            // MySQL/PostgreSQL allow multiple NULLs with unique constraint
            $table->unique('rapidapi_episode_id', 'tcgcsv_groups_rapidapi_episode_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            $table->dropUnique('tcgcsv_groups_rapidapi_episode_id_unique');
        });
    }
};
