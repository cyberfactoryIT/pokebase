<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Organization;
use App\Models\PricingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSubscriptionHelpersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_without_organization_is_free_tier()
    {
        $user = User::factory()->create([
            'organization_id' => null,
        ]);

        $this->assertEquals('free', $user->subscriptionTier());
        $this->assertTrue($user->isFree());
        $this->assertFalse($user->isAdvanced());
        $this->assertFalse($user->isPremium());
    }

    /** @test */
    public function user_with_organization_without_plan_is_free_tier()
    {
        $org = Organization::factory()->create([
            'pricing_plan_id' => null,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $this->assertEquals('free', $user->subscriptionTier());
        $this->assertTrue($user->isFree());
    }

    /** @test */
    public function user_with_premium_plan_is_premium_tier()
    {
        $plan = PricingPlan::factory()->create([
            'name' => 'Premium Plan',
        ]);
        
        $org = Organization::factory()->create([
            'pricing_plan_id' => $plan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $this->assertEquals('premium', $user->subscriptionTier());
        $this->assertTrue($user->isPremium());
        $this->assertFalse($user->isFree());
        $this->assertFalse($user->isAdvanced());
    }

    /** @test */
    public function user_with_advanced_plan_is_advanced_tier()
    {
        $plan = PricingPlan::factory()->create([
            'name' => 'Advanced Plan',
        ]);
        
        $org = Organization::factory()->create([
            'pricing_plan_id' => $plan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $this->assertEquals('advanced', $user->subscriptionTier());
        $this->assertTrue($user->isAdvanced());
        $this->assertFalse($user->isFree());
        $this->assertFalse($user->isPremium());
    }

    /** @test */
    public function user_with_pro_plan_is_advanced_tier()
    {
        $plan = PricingPlan::factory()->create([
            'name' => 'Pro Plan',
        ]);
        
        $org = Organization::factory()->create([
            'pricing_plan_id' => $plan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $this->assertEquals('advanced', $user->subscriptionTier());
        $this->assertTrue($user->isAdvanced());
    }

    /** @test */
    public function membership_status_returns_correct_data_for_free_user()
    {
        $user = User::factory()->create([
            'organization_id' => null,
        ]);

        $status = $user->membershipStatus();

        $this->assertEquals('free', $status['tier']);
        $this->assertEquals('active', $status['status']);
        $this->assertNull($status['billing_period']);
        $this->assertNull($status['next_renewal']);
        $this->assertFalse($status['is_cancelled']);
    }

    /** @test */
    public function membership_status_returns_correct_data_for_active_subscription()
    {
        $plan = PricingPlan::factory()->create([
            'name' => 'Premium Plan',
            'billing_period' => 'monthly',
        ]);
        
        $renewDate = now()->addMonth();
        $org = Organization::factory()->create([
            'pricing_plan_id' => $plan->id,
            'subscription_cancelled' => false,
            'renew_date' => $renewDate,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $status = $user->membershipStatus();

        $this->assertEquals('premium', $status['tier']);
        $this->assertEquals('Premium Plan', $status['plan_name']);
        $this->assertEquals('active', $status['status']);
        $this->assertEquals('monthly', $status['billing_period']);
        $this->assertEquals($renewDate->toDateString(), $status['next_renewal']->toDateString());
        $this->assertFalse($status['is_cancelled']);
    }

    /** @test */
    public function membership_status_returns_cancelled_for_cancelled_subscription()
    {
        $plan = PricingPlan::factory()->create([
            'name' => 'Premium Plan',
        ]);
        
        $cancelDate = now()->subDays(5);
        $org = Organization::factory()->create([
            'pricing_plan_id' => $plan->id,
            'subscription_cancelled' => true,
            'cancellation_subscription_date' => $cancelDate,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $status = $user->membershipStatus();

        $this->assertEquals('cancelled', $status['status']);
        $this->assertTrue($status['is_cancelled']);
        $this->assertEquals($cancelDate->toDateString(), $status['cancellation_date']->toDateString());
    }

    /** @test */
    public function case_insensitive_plan_name_matching()
    {
        // Test PREMIUM in uppercase
        $plan = PricingPlan::factory()->create(['name' => 'PREMIUM PACKAGE']);
        $org = Organization::factory()->create(['pricing_plan_id' => $plan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);
        
        $this->assertTrue($user->isPremium());

        // Test Advanced with mixed case
        $plan2 = PricingPlan::factory()->create(['name' => 'AdVaNcEd PlAn']);
        $org2 = Organization::factory()->create(['pricing_plan_id' => $plan2->id]);
        $user2 = User::factory()->create(['organization_id' => $org2->id]);
        
        $this->assertTrue($user2->isAdvanced());
    }
}
