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
            $table->text('logo_url')->nullable()->after('abbreviation');
            $table->unsignedInteger('rapidapi_episode_id')->nullable()->after('logo_url');
            
            $table->index('rapidapi_episode_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgcsv_groups', function (Blueprint $table) {
            $table->dropColumn(['logo_url', 'rapidapi_episode_id']);
        });
    }
};
