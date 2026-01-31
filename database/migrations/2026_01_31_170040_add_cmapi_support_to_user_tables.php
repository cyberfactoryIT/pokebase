<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add CMAPI (Lorcana/One Piece) card support to user interaction tables.
     * Extends dual-backend pattern to support CardMarket API cards alongside TCGDEX/TCGCSV.
     */
    public function up(): void
    {
        // Add cmapi_card_id to user_collection
        Schema::table('user_collection', function (Blueprint $table) {
            $table->string('cmapi_card_id', 100)->nullable()->after('product_id');
            
            $table->foreign('cmapi_card_id')
                  ->references('cmapi_id')
                  ->on('cmapi_cards')
                  ->onDelete('cascade');
            
            $table->index('cmapi_card_id');
        });

        // Add cmapi_card_id to deck_cards
        Schema::table('deck_cards', function (Blueprint $table) {
            $table->string('cmapi_card_id', 100)->nullable()->after('product_id');
            
            $table->foreign('cmapi_card_id')
                  ->references('cmapi_id')
                  ->on('cmapi_cards')
                  ->onDelete('cascade');
            
            $table->index('cmapi_card_id');
        });

        // Add cmapi_card_id to user_likes
        Schema::table('user_likes', function (Blueprint $table) {
            $table->string('cmapi_card_id', 100)->nullable()->after('product_id');
            
            $table->foreign('cmapi_card_id')
                  ->references('cmapi_id')
                  ->on('cmapi_cards')
                  ->onDelete('cascade');
            
            $table->index('cmapi_card_id');
        });

        // Add cmapi_card_id to user_wishlist_items
        Schema::table('user_wishlist_items', function (Blueprint $table) {
            $table->string('cmapi_card_id', 100)->nullable()->after('product_id');
            
            $table->foreign('cmapi_card_id')
                  ->references('cmapi_id')
                  ->on('cmapi_cards')
                  ->onDelete('cascade');
            
            $table->index('cmapi_card_id');
        });

        // Add cmapi_card_id to user_watch_items
        Schema::table('user_watch_items', function (Blueprint $table) {
            $table->string('cmapi_card_id', 100)->nullable()->after('product_id');
            
            $table->foreign('cmapi_card_id')
                  ->references('cmapi_id')
                  ->on('cmapi_cards')
                  ->onDelete('cascade');
            
            $table->index('cmapi_card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_collection', function (Blueprint $table) {
            $table->dropForeign(['cmapi_card_id']);
            $table->dropIndex(['cmapi_card_id']);
            $table->dropColumn('cmapi_card_id');
        });

        Schema::table('deck_cards', function (Blueprint $table) {
            $table->dropForeign(['cmapi_card_id']);
            $table->dropIndex(['cmapi_card_id']);
            $table->dropColumn('cmapi_card_id');
        });

        Schema::table('user_likes', function (Blueprint $table) {
            $table->dropForeign(['cmapi_card_id']);
            $table->dropIndex(['cmapi_card_id']);
            $table->dropColumn('cmapi_card_id');
        });

        Schema::table('user_wishlist_items', function (Blueprint $table) {
            $table->dropForeign(['cmapi_card_id']);
            $table->dropIndex(['cmapi_card_id']);
            $table->dropColumn('cmapi_card_id');
        });

        Schema::table('user_watch_items', function (Blueprint $table) {
            $table->dropForeign(['cmapi_card_id']);
            $table->dropIndex(['cmapi_card_id']);
            $table->dropColumn('cmapi_card_id');
        });
    }
};
