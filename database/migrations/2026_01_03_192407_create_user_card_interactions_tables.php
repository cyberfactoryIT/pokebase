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
        // User Likes table
        Schema::create('user_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key to tcgcsv_products
            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('tcgcsv_products')
                  ->onDelete('cascade');
            
            // Unique constraint and indexes
            $table->unique(['user_id', 'product_id']);
            $table->index('product_id');
            $table->index('user_id');
        });

        // User Wishlist Items table
        Schema::create('user_wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('tcgcsv_products')
                  ->onDelete('cascade');
            
            $table->unique(['user_id', 'product_id']);
            $table->index('product_id');
            $table->index('user_id');
        });

        // User Watch Items table
        Schema::create('user_watch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('tcgcsv_products')
                  ->onDelete('cascade');
            
            $table->unique(['user_id', 'product_id']);
            $table->index('product_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_watch_items');
        Schema::dropIfExists('user_wishlist_items');
        Schema::dropIfExists('user_likes');
    }
};
