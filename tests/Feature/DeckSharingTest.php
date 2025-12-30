<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Organization;
use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeckSharingTest extends TestCase
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
    public function free_user_cannot_share_deck()
    {
        $user = $this->createUserWithTier('free');

        $deck = Deck::create([
            'name' => 'Test Deck',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('decks.share', $deck))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertFalse($deck->fresh()->is_shared);
    }

    /** @test */
    public function advanced_user_can_share_one_deck()
    {
        $user = $this->createUserWithTier('advanced');

        $deck = Deck::create([
            'name' => 'Test Deck',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);

        $response = $this->actingAs($user)
            ->post(route('decks.share', $deck));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $deck->refresh();
        $this->assertTrue($deck->is_shared);
        $this->assertNotNull($deck->shared_token);
        $this->assertNotNull($deck->shared_at);
    }

    /** @test */
    public function advanced_user_cannot_share_second_deck()
    {
        $user = $this->createUserWithTier('advanced');

        // Create and share first deck
        $deck1 = Deck::create([
            'name' => 'Test Deck 1',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
        $deck1->share();

        // Try to share second deck
        $deck2 = Deck::create([
            'name' => 'Test Deck 2',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('decks.share', $deck2))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertFalse($deck2->fresh()->is_shared);
    }

    /** @test */
    public function advanced_user_can_unshare_and_share_another_deck()
    {
        $user = $this->createUserWithTier('advanced');

        // Create and share first deck
        $deck1 = Deck::create([
            'name' => 'Test Deck 1',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
        $deck1->share();

        // Unshare first deck
        $this->actingAs($user)
            ->post(route('decks.unshare', $deck1))
            ->assertRedirect();

        $this->assertFalse($deck1->fresh()->is_shared);

        // Now can share second deck
        $deck2 = Deck::create([
            'name' => 'Test Deck 2',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('decks.share', $deck2))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($deck2->fresh()->is_shared);
    }

    /** @test */
    public function premium_user_can_share_multiple_decks()
    {
        $user = $this->createUserWithTier('premium');

        $deck1 = Deck::create([
            'name' => 'Test Deck 1',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);

        $deck2 = Deck::create([
            'name' => 'Test Deck 2',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);

        $deck3 = Deck::create([
            'name' => 'Test Deck 3',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);

        // Share all three decks
        $this->actingAs($user)
            ->post(route('decks.share', $deck1))
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('decks.share', $deck2))
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('decks.share', $deck3))
            ->assertSessionHas('success');

        $this->assertTrue($deck1->fresh()->is_shared);
        $this->assertTrue($deck2->fresh()->is_shared);
        $this->assertTrue($deck3->fresh()->is_shared);
    }

    /** @test */
    public function only_owner_can_share_deck()
    {
        $owner = $this->createUserWithTier('premium');

        $otherUser = $this->createUserWithTier('premium');

        $deck = Deck::create([
            'name' => 'Test Deck',
            'user_id' => $owner->id,
            'game_id' => 1,
        ]);

        // Other user tries to share
        $this->actingAs($otherUser)
            ->post(route('decks.share', $deck))
            ->assertForbidden();

        $this->assertFalse($deck->fresh()->is_shared);
    }

    /** @test */
    public function only_owner_can_unshare_deck()
    {
        $owner = $this->createUserWithTier('premium');

        $otherUser = $this->createUserWithTier('premium');

        $deck = Deck::create([
            'name' => 'Test Deck',
            'user_id' => $owner->id,
            'game_id' => 1,
        ]);
        $deck->share();

        // Other user tries to unshare
        $this->actingAs($otherUser)
            ->post(route('decks.unshare', $deck))
            ->assertForbidden();

        $this->assertTrue($deck->fresh()->is_shared);
    }

    /** @test */
    public function public_link_works_while_shared()
    {
        $user = $this->createUserWithTier('premium');

        $deck = Deck::create([
            'name' => 'Test Deck',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
        $deck->share();

        $response = $this->get(route('decks.public', ['token' => $deck->shared_token]));

        $response->assertOk();
        $response->assertViewIs('decks.public');
        $response->assertViewHas('deck', $deck);
    }

    /** @test */
    public function public_link_returns_404_after_unshare()
    {
        $user = $this->createUserWithTier('premium');

        $deck = Deck::create([
            'name' => 'Test Deck',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
        $deck->share();
        $token = $deck->shared_token;

        // Unshare the deck
        $deck->unshare();

        $response = $this->get(route('decks.public', ['token' => $token]));

        $response->assertNotFound();
    }

    /** @test */
    public function public_link_is_accessible_without_login()
    {
        $user = $this->createUserWithTier('premium');

        $deck = Deck::create([
            'name' => 'Test Deck',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
        $deck->share();

        // Access as guest (not logged in)
        $response = $this->get(route('decks.public', ['token' => $deck->shared_token]));

        $response->assertOk();
        $response->assertViewIs('decks.public');
    }

    /** @test */
    public function shared_token_is_unique()
    {
        $user = $this->createUserWithTier('premium');

        $deck1 = Deck::create([
            'name' => 'Test Deck 1',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
        $deck1->share();

        $deck2 = Deck::create([
            'name' => 'Test Deck 2',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
        $deck2->share();

        $this->assertNotEquals($deck1->shared_token, $deck2->shared_token);
    }

    /** @test */
    public function unsharing_clears_token_and_timestamp()
    {
        $user = $this->createUserWithTier('premium');

        $deck = Deck::create([
            'name' => 'Test Deck',
            'user_id' => $user->id,
            'game_id' => 1,
        ]);
        $deck->share();

        $this->assertTrue($deck->is_shared);
        $this->assertNotNull($deck->shared_token);
        $this->assertNotNull($deck->shared_at);

        $deck->unshare();

        $this->assertFalse($deck->is_shared);
        $this->assertNull($deck->shared_token);
        $this->assertNull($deck->shared_at);
    }
}
