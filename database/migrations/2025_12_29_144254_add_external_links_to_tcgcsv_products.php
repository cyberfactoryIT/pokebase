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
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('tcgcsv_products', 'tcgo_url')) {
                $table->text('tcgo_url')->nullable()->after('cardmarket_price_updated_at');
            }
            if (!Schema::hasColumn('tcgcsv_products', 'cardmarket_url')) {
                $table->text('cardmarket_url')->nullable()->after('tcgo_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcgcsv_products', function (Blueprint $table) {
            $table->dropColumn(['tcgo_url', 'cardmarket_url']);
        });
    }
};
