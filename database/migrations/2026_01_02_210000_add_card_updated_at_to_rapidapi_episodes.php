<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapidapi_episodes', function (Blueprint $table) {
            $table->timestamp('cards_updated_at')->nullable()->after('cards_printed_total');
        });
    }

    public function down(): void
    {
        Schema::table('rapidapi_episodes', function (Blueprint $table) {
            $table->dropColumn('cards_updated_at');
        });
    }
};
