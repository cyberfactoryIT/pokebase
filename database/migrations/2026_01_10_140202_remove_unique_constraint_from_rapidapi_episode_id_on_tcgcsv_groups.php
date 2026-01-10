<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            // Remove unique constraint to allow many-to-many relationship
            $table->dropUnique('tcgcsv_groups_rapidapi_episode_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clean up duplicates before re-adding unique constraint
        // Keep only the first occurrence of each rapidapi_episode_id, set others to NULL
        DB::statement("
            UPDATE tcgcsv_groups g1
            LEFT JOIN (
                SELECT MIN(id) as min_id, rapidapi_episode_id
                FROM tcgcsv_groups
                WHERE rapidapi_episode_id IS NOT NULL
                GROUP BY rapidapi_episode_id
            ) g2 ON g1.id = g2.min_id AND g1.rapidapi_episode_id = g2.rapidapi_episode_id
            SET g1.rapidapi_episode_id = NULL
            WHERE g1.rapidapi_episode_id IS NOT NULL AND g2.min_id IS NULL
        ");
        
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            // Re-add unique constraint after cleanup
            $table->unique('rapidapi_episode_id', 'tcgcsv_groups_rapidapi_episode_id_unique');
        });
    }
};
