<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\PricingPlan;
use App\Models\DeckEvaluationPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class PriceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that free users without active deck evaluation cannot see prices.
     */
    public function test_free_user_without_deck_evaluation_cannot_see_prices(): void
    {
        // Create organization with free plan
        $freePlan = PricingPlan::factory()->free()->create();
        
        $organization = Organization::factory()->create([
            'pricing_plan_id' => $freePlan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
        
        $this->actingAs($user);
        
        // User should not be able to see prices
        $this->assertFalse($user->canSeePrices());
        $this->assertFalse(Gate::allows('seePrices'));
    }

    /**
     * Test that Advanced users can see prices.
     */
    public function test_advanced_user_can_see_prices(): void
    {
        // Create organization with Advanced plan
        $advancedPlan = PricingPlan::factory()->advanced()->create();
        
        $organization = Organization::factory()->create([
            'pricing_plan_id' => $advancedPlan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
        
        $this->actingAs($user);
        
        // User should be able to see prices
        $this->assertTrue($user->canSeePrices());
        $this->assertTrue(Gate::allows('seePrices'));
    }

    /**
     * Test that Premium users can see prices.
     */
    public function test_premium_user_can_see_prices(): void
    {
        // Create organization with Premium plan
        $premiumPlan = PricingPlan::factory()->premium()->create();
        
        $organization = Organization::factory()->create([
            'pricing_plan_id' => $premiumPlan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
        
        $this->actingAs($user);
        
        // User should be able to see prices
        $this->assertTrue($user->canSeePrices());
        $this->assertTrue(Gate::allows('seePrices'));
    }

    /**
     * Test that free users with active deck evaluation purchase can see prices.
     */
    public function test_free_user_with_active_deck_evaluation_can_see_prices(): void
    {
        // Create organization with free plan
        $freePlan = PricingPlan::factory()->free()->create();
        
        $organization = Organization::factory()->create([
            'pricing_plan_id' => $freePlan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
        
        // Create an active deck evaluation purchase (within last 365 days)
        DeckEvaluationPurchase::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'created_at' => now()->subDays(30), // 30 days ago
        ]);
        
        $this->actingAs($user);
        
        // User should be able to see prices due to active deck evaluation purchase
        $this->assertTrue($user->canSeePrices());
        $this->assertTrue(Gate::allows('seePrices'));
    }

    /**
     * Test that free users with expired deck evaluation purchase cannot see prices.
     */
    public function test_free_user_with_expired_deck_evaluation_cannot_see_prices(): void
    {
        // Create organization with free plan
        $freePlan = PricingPlan::factory()->free()->create();
        
        $organization = Organization::factory()->create([
            'pricing_plan_id' => $freePlan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
        
        // Create an expired deck evaluation purchase (older than 365 days)
        DeckEvaluationPurchase::factory()->create([
            'user_id' => $user->id,
            'status' => 'expired',
            'created_at' => now()->subDays(400), // 400 days ago
        ]);
        
        $this->actingAs($user);
        
        // User should not be able to see prices due to expired deck evaluation purchase
        $this->assertFalse($user->canSeePrices());
        $this->assertFalse(Gate::allows('seePrices'));
    }

    /**
     * Test that users without organization cannot see prices.
     */
    public function test_user_without_organization_cannot_see_prices(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);
        
        // User should not be able to see prices (no organization)
        $this->assertFalse($user->canSeePrices());
        $this->assertFalse(Gate::allows('seePrices'));
    }

    /**
     * Test that Deck Evaluation routes remain accessible and are not affected by price gating.
     */
    public function test_deck_evaluation_routes_not_modified(): void
    {
        // Create organization with free plan
        $freePlan = PricingPlan::factory()->free()->create();
        
        $organization = Organization::factory()->create([
            'pricing_plan_id' => $freePlan->id,
        ]);
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
        
        $this->actingAs($user);
        
        // Verify user cannot see prices normally
        $this->assertFalse($user->canSeePrices());
        
        // Test that Deck Evaluation routes are accessible
        $response = $this->get(route('deck-evaluation.packages.index'));
        $response->assertStatus(200);
        $response->assertDontSee('__("prices.hidden.title")'); // Ensure no price gating messages
    }
}
