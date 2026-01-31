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
        // Add price cache columns to user_collection
        Schema::table('user_collection', function (Blueprint $table) {
            $table->decimal('cached_price', 10, 2)->nullable()->after('notes');
            $table->string('cached_price_currency', 3)->default('USD')->after('cached_price');
            $table->timestamp('cached_price_updated_at')->nullable()->after('cached_price_currency');
            
            // Add index for cache refresh queries
            $table->index('cached_price_updated_at');
        });
        
        // Add price cache columns to deck_cards
        Schema::table('deck_cards', function (Blueprint $table) {
            $table->decimal('cached_price', 10, 2)->nullable()->after('quantity');
            $table->string('cached_price_currency', 3)->default('USD')->after('cached_price');
            $table->timestamp('cached_price_updated_at')->nullable()->after('cached_price_currency');
            
            // Add index for cache refresh queries
            $table->index('cached_price_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_collection', function (Blueprint $table) {
            $table->dropIndex(['cached_price_updated_at']);
            $table->dropColumn(['cached_price', 'cached_price_currency', 'cached_price_updated_at']);
        });
        
        Schema::table('deck_cards', function (Blueprint $table) {
            $table->dropIndex(['cached_price_updated_at']);
            $table->dropColumn(['cached_price', 'cached_price_currency', 'cached_price_updated_at']);
        });
    }
};
