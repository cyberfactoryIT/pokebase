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
        // Add trial support to promotions table
        Schema::table('promotions', function (Blueprint $table) {
            // Change type enum to include 'trial'
            DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM('percent', 'fixed', 'trial') NOT NULL DEFAULT 'percent'");
            
            // Trial-specific fields (only if they don't exist)
            if (!Schema::hasColumn('promotions', 'trial_plan_id')) {
                $table->unsignedBigInteger('trial_plan_id')->nullable();
            }
            if (!Schema::hasColumn('promotions', 'trial_duration_days')) {
                $table->integer('trial_duration_days')->nullable();
            }
        });
        
        // Add foreign key if not exists
        $promotionsFks = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys('promotions');
        $hasFk = collect($promotionsFks)->contains(function ($fk) {
            return $fk->getLocalColumns() === ['trial_plan_id'];
        });
        if (!$hasFk && Schema::hasColumn('promotions', 'trial_plan_id')) {
            Schema::table('promotions', function (Blueprint $table) {
                $table->foreign('trial_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
            });
        }
        
        // Add trial tracking to organizations table
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'trial_plan_id')) {
                $table->unsignedBigInteger('trial_plan_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'trial_expires_at')) {
                $table->timestamp('trial_expires_at')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'trial_promotion_id')) {
                $table->unsignedBigInteger('trial_promotion_id')->nullable();
            }
        });
        
        // Add foreign keys and index if not exists
        $organizationsFks = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys('organizations');
        
        $hasTrialPlanFk = collect($organizationsFks)->contains(function ($fk) {
            return $fk->getLocalColumns() === ['trial_plan_id'];
        });
        $hasTrialPromoFk = collect($organizationsFks)->contains(function ($fk) {
            return $fk->getLocalColumns() === ['trial_promotion_id'];
        });
        
        Schema::table('organizations', function (Blueprint $table) use ($hasTrialPlanFk, $hasTrialPromoFk) {
            if (!$hasTrialPlanFk && Schema::hasColumn('organizations', 'trial_plan_id')) {
                $table->foreign('trial_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
            }
            if (!$hasTrialPromoFk && Schema::hasColumn('organizations', 'trial_promotion_id')) {
                $table->foreign('trial_promotion_id')->references('id')->on('promotions')->onDelete('set null');
            }
            
            // Index for checking expired trials
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes('organizations');
            $hasIndex = collect($indexes)->contains(function ($index) {
                return in_array('trial_expires_at', $index->getColumns());
            });
            if (!$hasIndex && Schema::hasColumn('organizations', 'trial_expires_at')) {
                $table->index('trial_expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['trial_plan_id']);
            $table->dropForeign(['trial_promotion_id']);
            $table->dropIndex(['trial_expires_at']);
            $table->dropColumn(['trial_plan_id', 'trial_expires_at', 'trial_promotion_id']);
        });
        
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['trial_plan_id']);
            $table->dropColumn(['trial_plan_id', 'trial_duration_days']);
            
            // Revert type enum (note: this will fail if 'trial' types exist)
            $table->enum('type', ['percent', 'fixed'])->default('percent')->change();
        });
    }
};
