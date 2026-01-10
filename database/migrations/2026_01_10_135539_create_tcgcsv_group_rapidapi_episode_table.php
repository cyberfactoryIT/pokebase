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
        Schema::create('tcgcsv_group_rapidapi_episode', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tcgcsv_group_id');
            $table->unsignedInteger('rapidapi_episode_id');
            $table->timestamps();
            
            // Indexes
            $table->index('tcgcsv_group_id');
            $table->index('rapidapi_episode_id');
            
            // Unique constraint to prevent duplicate mappings
            $table->unique(['tcgcsv_group_id', 'rapidapi_episode_id'], 'group_episode_unique');
            
            // Foreign keys
            $table->foreign('tcgcsv_group_id')->references('id')->on('tcgcsv_groups')->onDelete('cascade');
            $table->foreign('rapidapi_episode_id')->references('episode_id')->on('rapidapi_episodes')->onDelete('cascade');
        });
        
        // Migrate existing data from rapidapi_episode_id column to pivot table
        DB::statement('
            INSERT INTO tcgcsv_group_rapidapi_episode (tcgcsv_group_id, rapidapi_episode_id, created_at, updated_at)
            SELECT id, rapidapi_episode_id, NOW(), NOW()
            FROM tcgcsv_groups
            WHERE rapidapi_episode_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcgcsv_group_rapidapi_episode');
    }
};
