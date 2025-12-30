<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Game;
use App\Models\Organization;
use App\Models\PricingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameActivationLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create pricing plans
        PricingPlan::factory()->free()->create(['name' => 'Free Plan']);
        PricingPlan::factory()->advanced()->create(['name' => 'Advanced Plan']);
        PricingPlan::factory()->premium()->create(['name' => 'Premium Plan']);
        
        // Create games - using database seeder data structure
        \DB::table('games')->insert([
            ['name' => 'Pokémon TCG', 'code' => 'pokemon', 'slug' => 'pokemon', 'tcgcsv_category_id' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Magic: The Gathering', 'code' => 'mtg', 'slug' => 'mtg', 'tcgcsv_category_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yu-Gi-Oh!', 'code' => 'yugioh', 'slug' => 'yugioh', 'tcgcsv_category_id' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Disney Lorcana', 'code' => 'lorcana', 'slug' => 'lorcana', 'tcgcsv_category_id' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /** @test */
    public function free_user_can_activate_only_one_game()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $games = Game::take(2)->get();
        
        // Verify user is on free tier
        $this->assertEquals('free', $user->subscriptionTier());
        $this->assertEquals(1, $user->maxActiveGames());
        
        // Try to activate 2 games
        $response = $this->actingAs($user)->post(route('profile.games.update'), [
            'games' => $games->pluck('id')->toArray(),
        ]);
        
        // Should be redirected with error
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        
        // User should have no games activated
        $this->assertEquals(0, $user->fresh()->games()->count());
    }

    /** @test */
    public function free_user_can_activate_one_game_successfully()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        $response = $this->actingAs($user)->post(route('profile.games.update'), [
            'games' => [$game->id],
        ]);
        
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'games-updated');
        
        $this->assertEquals(1, $user->fresh()->games()->count());
        $this->assertTrue($user->fresh()->canUseGame($game));
    }

    /** @test */
    public function advanced_user_can_activate_up_to_three_games()
    {
        $advancedPlan = PricingPlan::where('name', 'Advanced Plan')->first();
        $org = Organization::factory()->create(['pricing_plan_id' => $advancedPlan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);
        
        // Verify user is on advanced tier
        $this->assertEquals('advanced', $user->subscriptionTier());
        $this->assertEquals(3, $user->maxActiveGames());
        
        $games = Game::take(3)->get();
        
        $response = $this->actingAs($user)->post(route('profile.games.update'), [
            'games' => $games->pluck('id')->toArray(),
        ]);
        
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'games-updated');
        
        $this->assertEquals(3, $user->fresh()->games()->count());
    }

    /** @test */
    public function advanced_user_cannot_activate_fourth_game()
    {
        $advancedPlan = PricingPlan::where('name', 'Advanced Plan')->first();
        $org = Organization::factory()->create(['pricing_plan_id' => $advancedPlan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);
        
        $games = Game::take(4)->get();
        
        $response = $this->actingAs($user)->post(route('profile.games.update'), [
            'games' => $games->pluck('id')->toArray(),
        ]);
        
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        
        // Should not have any games activated (transaction rolled back)
        $this->assertEquals(0, $user->fresh()->games()->count());
    }

    /** @test */
    public function premium_user_can_activate_unlimited_games()
    {
        $premiumPlan = PricingPlan::where('name', 'Premium Plan')->first();
        $org = Organization::factory()->create(['pricing_plan_id' => $premiumPlan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);
        
        // Verify user is on premium tier
        $this->assertEquals('premium', $user->subscriptionTier());
        $this->assertNull($user->maxActiveGames()); // null = unlimited
        $this->assertTrue($user->canActivateAnotherGame());
        
        $allGames = Game::all();
        
        $response = $this->actingAs($user)->post(route('profile.games.update'), [
            'games' => $allGames->pluck('id')->toArray(),
        ]);
        
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'games-updated');
        
        $this->assertEquals($allGames->count(), $user->fresh()->games()->count());
    }

    /** @test */
    public function user_cannot_deactivate_all_games()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        // First activate one game
        $user->games()->attach($game->id);
        $this->assertEquals(1, $user->fresh()->games()->count());
        
        // Try to deactivate all games
        $response = $this->actingAs($user)->post(route('profile.games.update'), [
            'games' => [],
        ]);
        
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error');
        
        // User should still have the game
        $this->assertEquals(1, $user->fresh()->games()->count());
    }

    /** @test */
    public function gates_properly_check_game_activation_limits()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        // Free user with no games can activate one
        $this->assertTrue($user->canActivateAnotherGame());
        $this->assertTrue(\Gate::forUser($user)->allows('activateGame'));
        
        // After activating one game, cannot activate another
        $user->games()->attach($game->id);
        $this->assertFalse($user->fresh()->canActivateAnotherGame());
        $this->assertFalse(\Gate::forUser($user->fresh())->allows('activateGame'));
        
        // Can use the activated game
        $this->assertTrue($user->fresh()->canUseGame($game));
        $this->assertTrue(\Gate::forUser($user->fresh())->allows('useGame', $game));
        
        // Cannot use non-activated game
        $otherGame = Game::where('id', '!=', $game->id)->first();
        $this->assertFalse($user->fresh()->canUseGame($otherGame));
        $this->assertFalse(\Gate::forUser($user->fresh())->allows('useGame', $otherGame));
    }

    /** @test */
    public function deck_evaluation_routes_not_affected_by_game_limits()
    {
        // This test verifies that Deck Evaluation routes remain accessible
        // regardless of game activation limits
        
        $user = User::factory()->create(['organization_id' => null]);
        
        // User has no active games
        $this->assertEquals(0, $user->games()->count());
        
        // Deck Evaluation packages page should still be accessible
        $response = $this->actingAs($user)->get(route('deck-evaluation.packages.index'));
        $response->assertStatus(200);
        
        // This confirms Deck Evaluation is out of scope for game activation limits
        $this->assertTrue(true);
    }
}
