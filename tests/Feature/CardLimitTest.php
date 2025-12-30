<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\PricingPlan;
use App\Models\UserCollection;
use App\Models\Deck;
use App\Models\DeckCard;
use App\Models\TcgcsvProduct;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create pricing plans
        PricingPlan::factory()->free()->create(['name' => 'Free Plan']);
        PricingPlan::factory()->advanced()->create(['name' => 'Advanced Plan']);
        PricingPlan::factory()->premium()->create(['name' => 'Premium Plan']);
        
        // Create a game
        \DB::table('games')->insert([
            'name' => 'Pokémon TCG',
            'code' => 'pokemon',
            'slug' => 'pokemon',
            'tcgcsv_category_id' => 3,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Set card limit config
        config(['limits.cards.free' => 100]);
        
        // Ensure deck_cards table has product_id for SQLite tests
        if (\DB::connection()->getDriverName() === 'sqlite') {
            // Check if product_id column exists
            $columns = \DB::select("PRAGMA table_info(deck_cards)");
            $hasProductId = collect($columns)->contains('name', 'product_id');
            
            if (!$hasProductId) {
                // Drop and recreate table with correct schema
                \Schema::dropIfExists('deck_cards');
                \Schema::create('deck_cards', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('deck_id');
                    $table->unsignedBigInteger('product_id');
                    $table->integer('quantity')->default(1);
                    $table->timestamps();
                    $table->unique(['deck_id', 'product_id']);
                });
            }
        }
    }

    /** @test */
    public function free_user_can_add_cards_until_limit()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        // Verify user is on free tier
        $this->assertEquals('free', $user->subscriptionTier());
        $this->assertEquals(100, $user->cardLimit());
        
        // Add 99 cards to collection
        for ($i = 1; $i <= 99; $i++) {
            $product = $this->createProduct($game, "Card $i");
            UserCollection::create([
                'user_id' => $user->id,
                'product_id' => $product->product_id,
                'quantity' => 1,
            ]);
        }
        
        // Verify usage
        $this->assertEquals(99, $user->currentCardUsage());
        $this->assertEquals(1, $user->remainingCardSlots());
        $this->assertTrue($user->canAddMoreCards(1));
        
        // Add one more card (should succeed - reaches 100)
        $product = $this->createProduct($game, "Card 100");
        $response = $this->actingAs($user)->post(route('collection.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertEquals(100, $user->fresh()->currentCardUsage());
    }

    /** @test */
    public function free_user_cannot_add_101st_card()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        // Add 100 cards to collection
        for ($i = 1; $i <= 100; $i++) {
            $product = $this->createProduct($game, "Card $i");
            UserCollection::create([
                'user_id' => $user->id,
                'product_id' => $product->product_id,
                'quantity' => 1,
            ]);
        }
        
        $this->assertEquals(100, $user->currentCardUsage());
        $this->assertEquals(0, $user->remainingCardSlots());
        $this->assertFalse($user->canAddMoreCards(1));
        
        // Try to add 101st card (should fail)
        $product = $this->createProduct($game, "Card 101");
        $response = $this->actingAs($user)->post(route('collection.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(100, $user->fresh()->currentCardUsage());
    }

    /** @test */
    public function advanced_user_can_add_beyond_100_cards()
    {
        $advancedPlan = PricingPlan::where('name', 'Advanced Plan')->first();
        $org = Organization::factory()->create(['pricing_plan_id' => $advancedPlan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);
        $game = Game::first();
        
        $this->assertEquals('advanced', $user->subscriptionTier());
        $this->assertNull($user->cardLimit()); // unlimited
        $this->assertTrue($user->canAddMoreCards(150));
        
        // Add 150 cards to collection
        for ($i = 1; $i <= 150; $i++) {
            $product = $this->createProduct($game, "Card $i");
            UserCollection::create([
                'user_id' => $user->id,
                'product_id' => $product->product_id,
                'quantity' => 1,
            ]);
        }
        
        $this->assertEquals(150, $user->currentCardUsage());
        $this->assertNull($user->remainingCardSlots()); // unlimited
        
        // Add another card (should succeed)
        $product = $this->createProduct($game, "Card 151");
        $response = $this->actingAs($user)->post(route('collection.add'), [
            'product_id' => $product->product_id,
            'quantity' => 1,
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertEquals(151, $user->fresh()->currentCardUsage());
    }

    /** @test */
    public function premium_user_can_add_beyond_100_cards()
    {
        $premiumPlan = PricingPlan::where('name', 'Premium Plan')->first();
        $org = Organization::factory()->create(['pricing_plan_id' => $premiumPlan->id]);
        $user = User::factory()->create(['organization_id' => $org->id]);
        $game = Game::first();
        
        $this->assertEquals('premium', $user->subscriptionTier());
        $this->assertNull($user->cardLimit()); // unlimited
        
        // Add 200 cards
        for ($i = 1; $i <= 200; $i++) {
            $product = $this->createProduct($game, "Card $i");
            UserCollection::create([
                'user_id' => $user->id,
                'product_id' => $product->product_id,
                'quantity' => 1,
            ]);
        }
        
        $this->assertEquals(200, $user->currentCardUsage());
        $this->assertTrue($user->canAddMoreCards(100));
    }

    /** @test */
    public function card_count_includes_both_collection_and_deck_cards()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        // Add 50 unique cards to collection
        for ($i = 1; $i <= 50; $i++) {
            $product = $this->createProduct($game, "Collection Card $i");
            UserCollection::create([
                'user_id' => $user->id,
                'product_id' => $product->product_id,
                'quantity' => 1,
            ]);
        }
        
        // Create a deck and add 30 DIFFERENT cards
        $deck = Deck::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'name' => 'Test Deck',
        ]);
        
        for ($i = 1; $i <= 30; $i++) {
            $product = $this->createProduct($game, "Deck Card $i");
            DeckCard::create([
                'deck_id' => $deck->id,
                'product_id' => $product->product_id,
                'quantity' => 1,
            ]);
        }
        
        // Total should be 80 (50 unique collection + 30 unique deck)
        $this->assertEquals(80, $user->currentCardUsage());
        $this->assertEquals(20, $user->remainingCardSlots());
    }

    /** @test */
    public function same_card_in_collection_and_deck_is_counted_once()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        // Add 3x Pikachu to collection
        $pikachu = $this->createProduct($game, "Pikachu");
        UserCollection::create([
            'user_id' => $user->id,
            'product_id' => $pikachu->product_id,
            'quantity' => 3,
        ]);
        
        // Add 2x Pikachu to deck
        $deck = Deck::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'name' => 'Test Deck',
        ]);
        
        DeckCard::create([
            'deck_id' => $deck->id,
            'product_id' => $pikachu->product_id,
            'quantity' => 2,
        ]);
        
        // Should count 3 (max of 3 in collection and 2 in deck), NOT 5
        $this->assertEquals(3, $user->currentCardUsage());
        $this->assertEquals(97, $user->remainingCardSlots());
        
        // Add another different card to collection
        $charizard = $this->createProduct($game, "Charizard");
        UserCollection::create([
            'user_id' => $user->id,
            'product_id' => $charizard->product_id,
            'quantity' => 5,
        ]);
        
        // Total should be 8 (3 Pikachu + 5 Charizard)
        $this->assertEquals(8, $user->fresh()->currentCardUsage());
    }

    /** @test */
    public function adding_multiple_cards_at_once_respects_amount_check()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        // Add 95 cards
        for ($i = 1; $i <= 95; $i++) {
            $product = $this->createProduct($game, "Card $i");
            UserCollection::create([
                'user_id' => $user->id,
                'product_id' => $product->product_id,
                'quantity' => 1,
            ]);
        }
        
        $this->assertEquals(95, $user->currentCardUsage());
        $this->assertTrue($user->canAddMoreCards(5));
        $this->assertFalse($user->canAddMoreCards(6));
        
        // Try to add 6 cards at once (should fail)
        $product = $this->createProduct($game, "Bulk Card");
        $response = $this->actingAs($user)->post(route('collection.add'), [
            'product_id' => $product->product_id,
            'quantity' => 6,
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(95, $user->fresh()->currentCardUsage());
    }

    /** @test */
    public function adding_cards_to_deck_also_counts_toward_limit()
    {
        $user = User::factory()->create(['organization_id' => null]);
        $game = Game::first();
        
        // Add 98 cards to collection
        for ($i = 1; $i <= 98; $i++) {
            $product = $this->createProduct($game, "Card $i");
            UserCollection::create([
                'user_id' => $user->id,
                'product_id' => $product->product_id,
                'quantity' => 1,
            ]);
        }
        
        // Create a deck
        $deck = Deck::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'name' => 'Test Deck',
        ]);
        
        $this->assertEquals(98, $user->currentCardUsage());
        $this->assertTrue($user->canAddMoreCards(2));
        $this->assertFalse($user->canAddMoreCards(3));
        
        // Add 2 cards to deck (should succeed)
        $product1 = $this->createProduct($game, "Deck Card 1");
        $response = $this->actingAs($user)->post(route('decks.cards.add', $deck), [
            'product_id' => $product1->product_id,
            'quantity' => 1,
        ]);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        
        $product2 = $this->createProduct($game, "Deck Card 2");
        $response = $this->actingAs($user)->post(route('decks.cards.add', $deck), [
            'product_id' => $product2->product_id,
            'quantity' => 1,
        ]);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        
        $this->assertEquals(100, $user->fresh()->currentCardUsage());
        
        // Try to add one more to deck (should fail)
        $product3 = $this->createProduct($game, "Deck Card 3");
        $response = $this->actingAs($user)->post(route('decks.cards.add', $deck), [
            'product_id' => $product3->product_id,
            'quantity' => 1,
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(100, $user->fresh()->currentCardUsage());
    }

    /** @test */
    public function gate_correctly_authorizes_add_cards_action()
    {
        $user = User::factory()->create(['organization_id' => null]);
        
        // User with no cards can add
        $this->assertTrue(\Gate::forUser($user)->allows('addCards', 1));
        $this->assertTrue(\Gate::forUser($user)->allows('addCards', 100));
        $this->assertFalse(\Gate::forUser($user)->allows('addCards', 101));
    }

    // Helper method to create a product
    protected function createProduct($game, $name)
    {
        static $counter = 1;
        $productId = $counter++;
        
        \DB::table('tcgcsv_products')->insert([
            'product_id' => $productId,
            'group_id' => 1,
            'name' => $name,
            'clean_name' => \Str::slug($name),
            'image_url' => 'https://example.com/image.jpg',
            'category_id' => 3,
            'game_id' => $game->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return TcgcsvProduct::where('product_id', $productId)->first();
    }
}
