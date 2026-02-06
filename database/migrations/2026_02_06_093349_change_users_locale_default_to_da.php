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
        // Change default to NULL - will be set dynamically based on browser language
        DB::statement("ALTER TABLE users MODIFY COLUMN locale VARCHAR(8) DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original 'en' default
        DB::statement("ALTER TABLE users MODIFY COLUMN locale VARCHAR(8) DEFAULT 'en'");
    }
};
