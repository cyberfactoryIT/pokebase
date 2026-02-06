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
        // Modify promotions.type enum to include 'trial'
        DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM('percent', 'fixed', 'trial') NOT NULL DEFAULT 'percent'");
        
        // Add trial fields to promotions
        Schema::table('promotions', function (Blueprint $table) {
            $table->unsignedBigInteger('trial_plan_id')->nullable();
            $table->integer('trial_duration_days')->nullable();
            
            // Foreign key
            $table->foreign('trial_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
        });
        
        // Add trial fields to organizations
        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('trial_plan_id')->nullable();
            $table->timestamp('trial_expires_at')->nullable();
            $table->unsignedBigInteger('trial_promotion_id')->nullable();
            
            // Foreign keys
            $table->foreign('trial_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
            $table->foreign('trial_promotion_id')->references('id')->on('promotions')->onDelete('set null');
            
            // Index for checking expired trials
            $table->index('trial_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys and index from organizations
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['trial_plan_id']);
            $table->dropForeign(['trial_promotion_id']);
            $table->dropIndex(['trial_expires_at']);
            $table->dropColumn(['trial_plan_id', 'trial_expires_at', 'trial_promotion_id']);
        });
        
        // Drop foreign key and columns from promotions
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['trial_plan_id']);
            $table->dropColumn(['trial_plan_id', 'trial_duration_days']);
        });
        
        // Reset type enum
        DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent'");
    }
};
