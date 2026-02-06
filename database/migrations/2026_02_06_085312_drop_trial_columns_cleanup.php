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
        // Drop everything trial-related from organizations
        Schema::table('organizations', function (Blueprint $table) {
            // Drop foreign keys if they exist
            try {
                $table->dropForeign(['trial_plan_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
            
            try {
                $table->dropForeign(['trial_promotion_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
            
            // Drop index if exists
            try {
                $table->dropIndex(['trial_expires_at']);
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        });
        
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
        
        // Drop everything trial-related from promotions
        Schema::table('promotions', function (Blueprint $table) {
            // Drop foreign key if exists
            try {
                $table->dropForeign(['trial_plan_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
        });
        
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
        
        // Reset type enum to original (percent, fixed)
        DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is for cleanup only - no down needed
    }
};
