<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add tcgdex_card_id to user_likes
        Schema::table('user_likes', function (Blueprint $table) {
            // Make product_id nullable
            $table->unsignedBigInteger('product_id')->nullable()->change();
            
            // Add tcgdex_card_id if it doesn't exist
            if (!Schema::hasColumn('user_likes', 'tcgdex_card_id')) {
                $table->unsignedBigInteger('tcgdex_card_id')->nullable()->after('product_id');
                
                // Add foreign key to tcgdx_cards
                $table->foreign('tcgdex_card_id')
                      ->references('id')
                      ->on('tcgdx_cards')
                      ->onDelete('cascade');
            }
        });
        
        // Check and add indexes separately
        $indexes = DB::select("SHOW INDEXES FROM user_likes WHERE Key_name = 'user_likes_tcgdex_card_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_likes ADD INDEX user_likes_tcgdex_card_id_index (tcgdex_card_id)');
        }
        
        $indexes = DB::select("SHOW INDEXES FROM user_likes WHERE Key_name = 'user_likes_user_id_product_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_likes ADD INDEX user_likes_user_id_product_id_index (user_id, product_id)');
        }
        
        $indexes = DB::select("SHOW INDEXES FROM user_likes WHERE Key_name = 'user_likes_user_id_tcgdex_card_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_likes ADD INDEX user_likes_user_id_tcgdex_card_id_index (user_id, tcgdex_card_id)');
        }

        // Add tcgdex_card_id to user_wishlist_items
        Schema::table('user_wishlist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            
            if (!Schema::hasColumn('user_wishlist_items', 'tcgdex_card_id')) {
                $table->unsignedBigInteger('tcgdex_card_id')->nullable()->after('product_id');
                
                $table->foreign('tcgdex_card_id')
                      ->references('id')
                      ->on('tcgdx_cards')
                      ->onDelete('cascade');
            }
        });
        
        $indexes = DB::select("SHOW INDEXES FROM user_wishlist_items WHERE Key_name = 'user_wishlist_items_tcgdex_card_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_wishlist_items ADD INDEX user_wishlist_items_tcgdex_card_id_index (tcgdex_card_id)');
        }
        
        $indexes = DB::select("SHOW INDEXES FROM user_wishlist_items WHERE Key_name = 'user_wishlist_items_user_id_product_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_wishlist_items ADD INDEX user_wishlist_items_user_id_product_id_index (user_id, product_id)');
        }
        
        $indexes = DB::select("SHOW INDEXES FROM user_wishlist_items WHERE Key_name = 'user_wishlist_items_user_id_tcgdex_card_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_wishlist_items ADD INDEX user_wishlist_items_user_id_tcgdex_card_id_index (user_id, tcgdex_card_id)');
        }

        // Add tcgdex_card_id to user_watch_items
        Schema::table('user_watch_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            
            if (!Schema::hasColumn('user_watch_items', 'tcgdex_card_id')) {
                $table->unsignedBigInteger('tcgdex_card_id')->nullable()->after('product_id');
                
                $table->foreign('tcgdex_card_id')
                      ->references('id')
                      ->on('tcgdx_cards')
                      ->onDelete('cascade');
            }
        });
        
        $indexes = DB::select("SHOW INDEXES FROM user_watch_items WHERE Key_name = 'user_watch_items_tcgdex_card_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_watch_items ADD INDEX user_watch_items_tcgdex_card_id_index (tcgdex_card_id)');
        }
        
        $indexes = DB::select("SHOW INDEXES FROM user_watch_items WHERE Key_name = 'user_watch_items_user_id_product_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_watch_items ADD INDEX user_watch_items_user_id_product_id_index (user_id, product_id)');
        }
        
        $indexes = DB::select("SHOW INDEXES FROM user_watch_items WHERE Key_name = 'user_watch_items_user_id_tcgdex_card_id_index'");
        if (empty($indexes)) {
            DB::statement('ALTER TABLE user_watch_items ADD INDEX user_watch_items_user_id_tcgdex_card_id_index (user_id, tcgdex_card_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_likes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tcgdex_card_id']);
            $table->dropIndex(['user_id', 'product_id']);
            $table->dropForeign(['tcgdex_card_id']);
            $table->dropColumn('tcgdex_card_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::table('user_wishlist_items', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tcgdex_card_id']);
            $table->dropIndex(['user_id', 'product_id']);
            $table->dropForeign(['tcgdex_card_id']);
            $table->dropColumn('tcgdex_card_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::table('user_watch_items', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tcgdex_card_id']);
            $table->dropIndex(['user_id', 'product_id']);
            $table->dropForeign(['tcgdex_card_id']);
            $table->dropColumn('tcgdex_card_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->unique(['user_id', 'product_id']);
        });
    }
};
