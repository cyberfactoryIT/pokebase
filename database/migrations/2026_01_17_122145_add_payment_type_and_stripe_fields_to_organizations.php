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
        Schema::table('organizations', function (Blueprint $table) {
            // Payment type: subscription (recurring) or one_time (single purchase)
            $table->enum('payment_type', ['subscription', 'one_time'])->default('one_time')->after('billing_period');
            
            // Stripe subscription fields (only used when payment_type = 'subscription')
            $table->string('stripe_customer_id')->nullable()->after('payment_type');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            
            // Indexes for faster lookups
            $table->index('stripe_customer_id');
            $table->index('stripe_subscription_id');
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex(['stripe_customer_id']);
            $table->dropIndex(['stripe_subscription_id']);
            $table->dropIndex(['payment_type']);
            
            $table->dropColumn([
                'payment_type',
                'stripe_customer_id',
                'stripe_subscription_id',
            ]);
        });
    }
};
