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
        Schema::table('user_collection', function (Blueprint $table) {
            // Drop old unique constraint
            $table->dropUnique(['user_id', 'product_id', 'condition', 'is_foil']);
            
            // Add language column
            $table->string('language', 5)->default('en')->after('product_id');
            
            // Recreate unique constraint including language
            $table->unique(['user_id', 'product_id', 'language', 'condition', 'is_foil'], 'user_collection_unique');
            
            // Add index for language filtering
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_collection', function (Blueprint $table) {
            // Drop new constraint
            $table->dropUnique('user_collection_unique');
            $table->dropIndex(['language']);
            
            // Drop language column
            $table->dropColumn('language');
            
            // Restore old unique constraint
            $table->unique(['user_id', 'product_id', 'condition', 'is_foil']);
        });
    }
};
