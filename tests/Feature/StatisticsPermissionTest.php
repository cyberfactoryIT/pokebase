<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Organization;
use App\Models\PricingPlan;
use App\Models\TcgcsvProduct;
use App\Models\User;
use App\Models\UserCollection;
use App\Models\Deck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create pricing plans
        PricingPlan::factory()->free()->create(['name' => 'Free Plan']);
        PricingPlan::factory()->advanced()->create(['name' => 'Advanced Plan']);
        PricingPlan::factory()->premium()->create(['name' => 'Premium Plan']);
        
        // Set up test games
        \DB::table('games')->insert([
            ['id' => 1, 'name' => 'Pokemon', 'code' => 'pokemon', 'slug' => 'pokemon', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Create test product
        TcgcsvProduct::create([
            'product_id' => 1,
            'group_id' => 1,
            'game_id' => 1,
            'name' => 'Test Card',
            'category_id' => 3,
        ]);
    }

    protected function createUserWithTier(string $tier): User
    {
        $user = User::factory()->create();
        
        if ($tier !== 'free') {
            $plan = PricingPlan::where('name', ucfirst($tier) . ' Plan')->first();
            $organization = Organization::factory()->create([
                'name' => $user->name . "'s Organization",
                'pricing_plan_id' => $plan->id,
            ]);
            $user->update(['organization_id' => $organization->id]);
        }
        
        return $user;
    }

    /** @test */
    public function free_user_cannot_see_collection_mini_stats()
    {
        $user = $this->createUserWithTier('free');
        
        UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('collection.index'));

        $response->assertStatus(200);
        
        // Should NOT see the three mini-stat cards
        $response->assertDontSee('Rarity Distribution');
        $response->assertDontSee('Foil Cards');
        $response->assertDontSee('Top Set');
        
        // Should see upsell badge
        $response->assertSee(__('stats.upsell.collection_free_title'));
        $response->assertSee(__('stats.upsell.cta_upgrade'));
        
        // Should NOT see Statistics tab
        $response->assertDontSee(__('collection/index.tab_statistics'));
    }

    /** @test */
    public function advanced_user_can_see_collection_mini_stats_but_not_statistics_tab()
    {
        $user = $this->createUserWithTier('advanced');
        
        UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('collection.index'));

        $response->assertStatus(200);
        
        // Should see the three mini-stat cards
        $response->assertSee(__('collection/index.rarity_distribution'));
        $response->assertSee(__('collection/index.foil_cards'));
        $response->assertSee(__('collection/index.top_set'));
        
        // Should see "want more stats" upsell badge
        $response->assertSee(__('stats.upsell.collection_advanced_title'));
        $response->assertSee(__('stats.upsell.collection_advanced_body'));
        
        // Should NOT see Statistics tab
        $response->assertDontSee(__('collection/index.tab_statistics'));
    }

    /** @test */
    public function premium_user_can_see_collection_mini_stats_and_statistics_tab()
    {
        $user = $this->createUserWithTier('premium');
        
        UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('collection.index'));

        $response->assertStatus(200);
        
        // Should see the three mini-stat cards
        $response->assertSee(__('collection/index.rarity_distribution'));
        $response->assertSee(__('collection/index.foil_cards'));
        $response->assertSee(__('collection/index.top_set'));
        
        // Should NOT see upsell badge (premium has everything)
        $response->assertDontSee(__('stats.upsell.collection_advanced_title'));
        $response->assertDontSee(__('stats.upsell.collection_free_title'));
        
        // Should see Statistics tab
        $response->assertSee(__('collection/index.tab_statistics'));
    }

    /** @test */
    public function free_user_sees_first_row_deck_stats_only()
    {
        $user = $this->createUserWithTier('free');
        
        $deck = Deck::create([
            'user_id' => $user->id,
            'game_id' => 1,
            'name' => 'Test Deck',
            'description' => 'Test Description',
        ]);

        $response = $this->actingAs($user)->get(route('decks.show', $deck));

        $response->assertStatus(200);
        
        // Should NOT see second row stats headings
        $response->assertDontSee('Rarity Distribution');
        $response->assertDontSee('Top Sets');
        
        // Should see upsell badge for second row
        $response->assertSee(__('stats.upsell.deck_free_title'));
        $response->assertSee(__('stats.upsell.deck_free_body'));
    }

    /** @test */
    public function advanced_user_sees_both_deck_stat_rows()
    {
        $user = $this->createUserWithTier('advanced');
        
        $deck = Deck::create([
            'user_id' => $user->id,
            'game_id' => 1,
            'name' => 'Test Deck',
            'description' => 'Test Description',
        ]);

        $response = $this->actingAs($user)->get(route('decks.show', $deck));

        $response->assertStatus(200);
        
        // Should see second row stats headings
        $response->assertSee('Rarity Distribution');
        $response->assertSee('Top Sets');
        
        // Should NOT see upsell badge
        $response->assertDontSee(__('stats.upsell.deck_free_title'));
    }

    /** @test */
    public function premium_user_sees_both_deck_stat_rows()
    {
        $user = $this->createUserWithTier('premium');
        
        $deck = Deck::create([
            'user_id' => $user->id,
            'game_id' => 1,
            'name' => 'Test Deck',
            'description' => 'Test Description',
        ]);

        $response = $this->actingAs($user)->get(route('decks.show', $deck));

        $response->assertStatus(200);
        
        // Should see second row stats headings
        $response->assertSee('Rarity Distribution');
        $response->assertSee('Top Sets');
        
        // Should NOT see upsell badge
        $response->assertDontSee(__('stats.upsell.deck_free_title'));
    }

    /** @test */
    public function user_model_helper_methods_work_correctly()
    {
        $freeUser = $this->createUserWithTier('free');
        $advancedUser = $this->createUserWithTier('advanced');
        $premiumUser = $this->createUserWithTier('premium');

        // Test canSeeCollectionMiniStats
        $this->assertFalse($freeUser->canSeeCollectionMiniStats());
        $this->assertTrue($advancedUser->canSeeCollectionMiniStats());
        $this->assertTrue($premiumUser->canSeeCollectionMiniStats());

        // Test canSeeCollectionStatisticsTab
        $this->assertFalse($freeUser->canSeeCollectionStatisticsTab());
        $this->assertFalse($advancedUser->canSeeCollectionStatisticsTab());
        $this->assertTrue($premiumUser->canSeeCollectionStatisticsTab());

        // Test canSeeDeckSecondRowStats
        $this->assertFalse($freeUser->canSeeDeckSecondRowStats());
        $this->assertTrue($advancedUser->canSeeDeckSecondRowStats());
        $this->assertTrue($premiumUser->canSeeDeckSecondRowStats());
    }
}
