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
        Schema::create('user_card_photos', function (Blueprint $table) {
            $table->id();
            
            // Owner and association
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_collection_id'); // Attach to collection item (preferred)
            
            // File metadata
            $table->string('path'); // Storage path
            $table->string('original_filename');
            $table->string('mime_type', 50);
            $table->unsignedInteger('size_bytes');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
                
            $table->foreign('user_collection_id')
                ->references('id')
                ->on('user_collection')
                ->onDelete('cascade');
            
            // Indexes for quick lookups
            $table->index('user_id');
            $table->index('user_collection_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_card_photos');
    }
};
