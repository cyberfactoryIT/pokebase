<?php

namespace Tests\Feature;

use App\Models\PricingPlan;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pricing_page_loads_successfully()
    {
        // Create pricing plans
        PricingPlan::factory()->free()->create();
        PricingPlan::factory()->advanced()->create();
        PricingPlan::factory()->premium()->create();

        $response = $this->get(route('pricing'));

        $response->assertStatus(200);
    }

    /** @test */
    public function pricing_page_displays_all_three_plans()
    {
        // Create pricing plans
        PricingPlan::factory()->free()->create();
        PricingPlan::factory()->advanced()->create();
        PricingPlan::factory()->premium()->create();

        $response = $this->get(route('pricing'));

        $response->assertSee('Pricing');
        $response->assertSee('Free');
        $response->assertSee('Advanced');
        $response->assertSee('Premium');
    }

    /** @test */
    public function pricing_page_shows_prices_from_database()
    {
        // Create pricing plans with specific prices
        PricingPlan::factory()->create([
            'code' => 'free',
            'name' => 'Free',
            'monthly_price_cents' => 0,
            'currency' => 'EUR'
        ]);
        
        PricingPlan::factory()->create([
            'code' => 'advanced',
            'name' => 'Advanced',
            'monthly_price_cents' => 2500, // €25.00
            'yearly_price_cents' => 25000, // €250.00
            'currency' => 'EUR'
        ]);

        $response = $this->get(route('pricing'));

        $response->assertSee('Free'); // Free plan shows "Free" not "0"
        $response->assertSee('25,00'); // Monthly price for Advanced
    }

    /** @test */
    public function guest_sees_get_started_for_free_button()
    {
        PricingPlan::factory()->free()->create();
        PricingPlan::factory()->advanced()->create();
        PricingPlan::factory()->premium()->create();

        $response = $this->get(route('pricing'));

        $response->assertSee('Get started for free');
    }

    /** @test */
    public function authenticated_user_sees_current_plan_badge()
    {
        $freePlan = PricingPlan::factory()->free()->create();
        $advancedPlan = PricingPlan::factory()->advanced()->create();
        PricingPlan::factory()->premium()->create();

        $org = Organization::factory()->create(['pricing_plan_id' => $advancedPlan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($user)->get(route('pricing'));

        $response->assertSee('Current plan');
    }

    /** @test */
    public function pricing_page_displays_feature_comparison_matrix()
    {
        PricingPlan::factory()->free()->create();
        PricingPlan::factory()->advanced()->create();
        PricingPlan::factory()->premium()->create();

        $response = $this->get(route('pricing'));

        $response->assertSee('Compare plans');
        $response->assertSee('Catalog browsing');
        $response->assertSee('Collection & deck value');
        $response->assertSee('Statistics & insights');
        $response->assertSee('Active games');
        $response->assertSee('Cards limit');
        $response->assertSee('Deck sharing');
    }

    /** @test */
    public function pricing_page_displays_plan_descriptions()
    {
        PricingPlan::factory()->free()->create();
        PricingPlan::factory()->advanced()->create();
        PricingPlan::factory()->premium()->create();

        $response = $this->get(route('pricing'));

        $response->assertSee('Which plan is right for you?');
        $response->assertSee('Free is designed for getting started');
        $response->assertSee('Advanced is for active collectors');
        $response->assertSee('Premium is built for power users');
    }

    /** @test */
    public function free_user_sees_upgrade_buttons_for_paid_plans()
    {
        $freePlan = PricingPlan::factory()->free()->create();
        $advancedPlan = PricingPlan::factory()->advanced()->create();
        $premiumPlan = PricingPlan::factory()->premium()->create();

        $org = Organization::factory()->create(['pricing_plan_id' => $freePlan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($user)->get(route('pricing'));

        $response->assertSee('Upgrade to Advanced');
        $response->assertSee('Upgrade to Premium');
    }

    /** @test */
    public function advanced_user_only_sees_premium_upgrade_button()
    {
        $freePlan = PricingPlan::factory()->free()->create();
        $advancedPlan = PricingPlan::factory()->advanced()->create();
        $premiumPlan = PricingPlan::factory()->premium()->create();

        $org = Organization::factory()->create(['pricing_plan_id' => $advancedPlan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($user)->get(route('pricing'));

        $response->assertSee('Current plan'); // On Advanced card
        $response->assertSee('Upgrade to Premium');
        $response->assertDontSee('Upgrade to Advanced');
    }
}
