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
        // Change default locale from 'en' to 'da'
        DB::statement("ALTER TABLE users MODIFY COLUMN locale VARCHAR(8) DEFAULT 'da'");
        
        // Update existing users with 'en' to 'da' (optional - uncomment if needed)
        // DB::table('users')->where('locale', 'en')->update(['locale' => 'da']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to 'en' default
        DB::statement("ALTER TABLE users MODIFY COLUMN locale VARCHAR(8) DEFAULT 'en'");
    }
};
