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
        Schema::table('cmapi_price_history', function (Blueprint $table) {
            // Prices JSON field already exists, no changes needed
            // This migration is kept for tracking purposes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cmapi_price_history', function (Blueprint $table) {
            // No changes to revert
        });
    }
};
