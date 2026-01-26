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
        Schema::table('rapidapi_cards', function (Blueprint $table) {
            $table->string('visible_lookup_key', 64)->nullable()->after('raw_data');
            $table->index('visible_lookup_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapidapi_cards', function (Blueprint $table) {
            $table->dropIndex(['visible_lookup_key']);
            $table->dropColumn('visible_lookup_key');
        });
    }
};
