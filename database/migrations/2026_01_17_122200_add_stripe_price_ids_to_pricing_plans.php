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
        Schema::table('pricing_plans', function (Blueprint $table) {
            // Stripe recurring price IDs (for subscriptions)
            $table->string('stripe_monthly_price_id')->nullable()->after('monthly_price_cents');
            $table->string('stripe_yearly_price_id')->nullable()->after('yearly_price_cents');
            
            // Note: monthly_price_cents and yearly_price_cents are still used for one-time payments
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_monthly_price_id',
                'stripe_yearly_price_id',
            ]);
        });
    }
};
