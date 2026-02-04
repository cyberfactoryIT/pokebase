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
        // Add trial support to promotions table
        Schema::table('promotions', function (Blueprint $table) {
            // Change type enum to include 'trial'
            $table->enum('type', ['percent', 'fixed', 'trial'])->default('percent')->change();
            
            // Trial-specific fields
            $table->unsignedBigInteger('trial_plan_id')->nullable()->after('value');
            $table->integer('trial_duration_days')->nullable()->after('trial_plan_id');
            
            // Foreign key
            $table->foreign('trial_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
        });
        
        // Add trial tracking to organizations table
        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('trial_plan_id')->nullable()->after('current_plan_id');
            $table->timestamp('trial_expires_at')->nullable()->after('trial_plan_id');
            $table->unsignedBigInteger('trial_promotion_id')->nullable()->after('trial_expires_at');
            
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
