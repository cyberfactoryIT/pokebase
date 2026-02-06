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
        // Drop foreign keys from organizations (using raw SQL to ignore errors)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            DB::statement('ALTER TABLE organizations DROP FOREIGN KEY organizations_trial_plan_id_foreign');
        } catch (\Exception $e) {
            // FK doesn't exist, continue
        }
        
        try {
            DB::statement('ALTER TABLE organizations DROP FOREIGN KEY organizations_trial_promotion_id_foreign');
        } catch (\Exception $e) {
            // FK doesn't exist, continue
        }
        
        // Drop index from organizations
        try {
            DB::statement('ALTER TABLE organizations DROP INDEX organizations_trial_expires_at_index');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Drop columns from organizations if they exist
        if (Schema::hasColumn('organizations', 'trial_plan_id')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->dropColumn('trial_plan_id');
            });
        }
        if (Schema::hasColumn('organizations', 'trial_expires_at')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->dropColumn('trial_expires_at');
            });
        }
        if (Schema::hasColumn('organizations', 'trial_promotion_id')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->dropColumn('trial_promotion_id');
            });
        }
        
        // Drop foreign key from promotions
        try {
            DB::statement('ALTER TABLE promotions DROP FOREIGN KEY promotions_trial_plan_id_foreign');
        } catch (\Exception $e) {
            // FK doesn't exist, continue
        }
        
        // Drop columns from promotions if they exist
        if (Schema::hasColumn('promotions', 'trial_plan_id')) {
            Schema::table('promotions', function (Blueprint $table) {
                $table->dropColumn('trial_plan_id');
            });
        }
        if (Schema::hasColumn('promotions', 'trial_duration_days')) {
            Schema::table('promotions', function (Blueprint $table) {
                $table->dropColumn('trial_duration_days');
            });
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Reset type enum to original (percent, fixed) - only if 'trial' exists
        try {
            DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent'");
        } catch (\Exception $e) {
            // Enum doesn't need reset, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is for cleanup only - no down needed
    }
};
