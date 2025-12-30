<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DeckEvaluationPackage;
use App\Models\DeckEvaluationSession;
use App\Models\DeckEvaluationPurchase;
use App\Services\DeckEvaluationEntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DeckEvaluationEntitlementTest extends TestCase
{
    use RefreshDatabase;

    private DeckEvaluationEntitlementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DeckEvaluationEntitlementService::class);
        
        // Seed packages
        $this->artisan('db:seed', ['--class' => 'DeckEvaluationPackageSeeder']);
    }

    /** @test */
    public function guest_can_evaluate_up_to_10_cards_free()
    {
        $guestToken = DeckEvaluationSession::generateGuestToken();
        
        // First evaluation with 5 cards - should be allowed
        $check = $this->service->canEvaluate(null, $guestToken, 5);
        $this->assertTrue($check['allowed']);
        $this->assertEquals('free_evaluation', $check['reason']);
        $this->assertEquals(10, $check['remaining']);
        
        // Record the evaluation
        $cardIds = [1, 2, 3, 4, 5];
        $result = $this->service->recordEvaluation($check['session'], $cardIds);
        $this->assertTrue($result['success']);
        $this->assertFalse($result['is_duplicate']);
        
        // Second evaluation with 5 more cards - should be allowed
        $check2 = $this->service->canEvaluate(null, $guestToken, 5);
        $this->assertTrue($check2['allowed']);
        $this->assertEquals(5, $check2['remaining']);
    }

    /** @test */
    public function guest_cannot_exceed_10_cards_without_purchase()
    {
        $guestToken = DeckEvaluationSession::generateGuestToken();
        
        // Try to evaluate 11 cards - should be blocked
        $check = $this->service->canEvaluate(null, $guestToken, 11);
        $this->assertFalse($check['allowed']);
        $this->assertEquals('free_limit_exceeded', $check['reason']);
        $this->assertEquals(10, $check['remaining']);
    }

    /** @test */
    public function guest_blocked_after_using_10_free_cards()
    {
        $guestToken = DeckEvaluationSession::generateGuestToken();
        
        // Use all 10 free cards
        $check = $this->service->canEvaluate(null, $guestToken, 10);
        $this->assertTrue($check['allowed']);
        
        $cardIds = range(1, 10);
        $this->service->recordEvaluation($check['session'], $cardIds);
        
        // Try to evaluate 1 more - should require purchase
        $check2 = $this->service->canEvaluate(null, $guestToken, 1);
        $this->assertFalse($check2['allowed']);
        $this->assertEquals('purchase_required', $check2['reason']);
    }

    /** @test */
    public function purchase_100_cards_allows_up_to_100_within_30_days()
    {
        $guestToken = DeckEvaluationSession::generateGuestToken();
        $package = DeckEvaluationPackage::where('code', 'EVAL_100')->first();
        
        // Create purchase
        $purchase = DeckEvaluationPurchase::create([
            'guest_token' => $guestToken,
            'package_id' => $package->id,
            'purchased_at' => now(),
            'expires_at' => now()->addDays(30),
            'cards_limit' => 100,
            'cards_used' => 0,
            'status' => 'active',
        ]);
        
        // Evaluate 50 cards - should be allowed
        $check = $this->service->canEvaluate(null, $guestToken, 50);
        $this->assertTrue($check['allowed']);
        $this->assertEquals('active_purchase', $check['reason']);
        
        $cardIds = range(1, 50);
        $this->service->recordEvaluation($check['session'], $cardIds, $purchase);
        
        // Should have 50 cards remaining
        $purchase->refresh();
        $this->assertEquals(50, $purchase->cards_used);
        $this->assertEquals(50, $purchase->remaining_cards);
        
        // Evaluate 50 more - should be allowed
        $check2 = $this->service->canEvaluate(null, $guestToken, 50);
        $this->assertTrue($check2['allowed']);
        
        // Try to evaluate 1 more (total 101) - should be blocked
        $this->service->recordEvaluation($check['session'], range(51, 100), $purchase);
        $check3 = $this->service->canEvaluate(null, $guestToken, 1);
        $this->assertFalse($check3['allowed']);
        $this->assertEquals('insufficient_cards', $check3['reason']);
    }

    /** @test */
    public function unlimited_package_allows_multiple_evaluations_for_1_year()
    {
        $user = User::factory()->create();
        $package = DeckEvaluationPackage::where('code', 'EVAL_UNLIMITED')->first();
        
        // Create unlimited purchase
        $purchase = DeckEvaluationPurchase::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'purchased_at' => now(),
            'expires_at' => now()->addYear(),
            'cards_limit' => null, // Unlimited
            'cards_used' => 0,
            'status' => 'active',
        ]);
        
        // Evaluate 1000 cards - should be allowed
        $check = $this->service->canEvaluate($user->id, null, 1000);
        $this->assertTrue($check['allowed']);
        $this->assertEquals('active_purchase', $check['reason']);
        
        $cardIds = range(1, 1000);
        $result = $this->service->recordEvaluation($check['session'], $cardIds, $purchase);
        $this->assertTrue($result['success']);
        
        // Cards used should be tracked but no limit
        $purchase->refresh();
        $this->assertEquals(1000, $purchase->cards_used);
        $this->assertNull($purchase->remaining_cards);
        
        // Can still evaluate more
        $check2 = $this->service->canEvaluate($user->id, null, 5000);
        $this->assertTrue($check2['allowed']);
    }

    /** @test */
    public function expired_purchase_blocks_evaluation()
    {
        $guestToken = DeckEvaluationSession::generateGuestToken();
        $package = DeckEvaluationPackage::where('code', 'EVAL_100')->first();
        
        // Create expired purchase
        $purchase = DeckEvaluationPurchase::create([
            'guest_token' => $guestToken,
            'package_id' => $package->id,
            'purchased_at' => now()->subDays(31),
            'expires_at' => now()->subDay(),
            'cards_limit' => 100,
            'cards_used' => 50,
            'status' => 'active', // Will be marked expired
        ]);
        
        // Try to evaluate - should be blocked
        $check = $this->service->canEvaluate(null, $guestToken, 10);
        $this->assertFalse($check['allowed']);
        $this->assertEquals('purchase_expired', $check['reason']);
        
        // Purchase should be marked expired
        $purchase->refresh();
        $this->assertEquals('expired', $purchase->status);
    }

    /** @test */
    public function idempotency_prevents_double_counting()
    {
        $guestToken = DeckEvaluationSession::generateGuestToken();
        $package = DeckEvaluationPackage::where('code', 'EVAL_100')->first();
        
        $purchase = DeckEvaluationPurchase::create([
            'guest_token' => $guestToken,
            'package_id' => $package->id,
            'purchased_at' => now(),
            'expires_at' => now()->addDays(30),
            'cards_limit' => 100,
            'cards_used' => 0,
            'status' => 'active',
        ]);
        
        $session = $this->service->getOrCreateSession(null, $guestToken);
        $cardIds = [1, 2, 3, 4, 5];
        
        // First evaluation
        $result1 = $this->service->recordEvaluation($session, $cardIds, $purchase);
        $this->assertTrue($result1['success']);
        $this->assertFalse($result1['is_duplicate']);
        
        $purchase->refresh();
        $this->assertEquals(5, $purchase->cards_used);
        
        // Re-run same evaluation (same card IDs)
        $result2 = $this->service->recordEvaluation($session, $cardIds, $purchase);
        $this->assertTrue($result2['success']);
        $this->assertTrue($result2['is_duplicate']);
        
        // Cards used should NOT increase
        $purchase->refresh();
        $this->assertEquals(5, $purchase->cards_used);
    }

    /** @test */
    public function guest_data_can_be_claimed_by_registered_user()
    {
        $guestToken = DeckEvaluationSession::generateGuestToken();
        $package = DeckEvaluationPackage::where('code', 'EVAL_100')->first();
        
        // Create guest session and purchase
        $session = DeckEvaluationSession::create([
            'guest_token' => $guestToken,
            'status' => 'draft',
            'free_cards_limit' => 10,
            'free_cards_used' => 5,
        ]);
        
        $purchase = DeckEvaluationPurchase::create([
            'guest_token' => $guestToken,
            'package_id' => $package->id,
            'purchased_at' => now(),
            'expires_at' => now()->addDays(30),
            'cards_limit' => 100,
            'cards_used' => 20,
            'status' => 'active',
        ]);
        
        // User registers
        $user = User::factory()->create();
        
        // Claim guest data
        $result = $this->service->claimGuestData($user->id, $guestToken);
        
        $this->assertEquals(1, $result['sessions_claimed']);
        $this->assertEquals(1, $result['purchases_claimed']);
        
        // Verify data is now attached to user
        $session->refresh();
        $purchase->refresh();
        
        $this->assertEquals($user->id, $session->user_id);
        $this->assertEquals($user->id, $purchase->user_id);
        
        // Guest token preserved for reference
        $this->assertEquals($guestToken, $purchase->guest_token);
    }

    /** @test */
    public function entitlement_summary_shows_correct_status()
    {
        $guestToken = DeckEvaluationSession::generateGuestToken();
        
        // Free tier
        $summary = $this->service->getEntitlementSummary(null, $guestToken);
        $this->assertEquals('free', $summary['type']);
        $this->assertEquals(10, $summary['cards_limit']);
        $this->assertEquals(0, $summary['cards_used']);
        
        // With purchase
        $package = DeckEvaluationPackage::where('code', 'EVAL_600')->first();
        $purchase = DeckEvaluationPurchase::create([
            'guest_token' => $guestToken,
            'package_id' => $package->id,
            'purchased_at' => now(),
            'expires_at' => now()->addDays(30),
            'cards_limit' => 600,
            'cards_used' => 100,
            'status' => 'active',
        ]);
        
        $summary = $this->service->getEntitlementSummary(null, $guestToken);
        $this->assertEquals('purchased', $summary['type']);
        $this->assertEquals(600, $summary['cards_limit']);
        $this->assertEquals(100, $summary['cards_used']);
        $this->assertEquals(500, $summary['cards_remaining']);
        $this->assertFalse($summary['is_unlimited']);
    }

    /** @test */
    public function user_with_active_membership_can_also_have_deck_evaluation_purchase()
    {
        // Regular user (membership is tracked via organization/pricing_plan)
        $user = User::factory()->create();
        
        // User purchases deck evaluation package
        $package = DeckEvaluationPackage::where('code', 'EVAL_100')->first();
        $purchase = DeckEvaluationPurchase::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'purchased_at' => now(),
            'expires_at' => now()->addDays(30),
            'cards_limit' => 100,
            'status' => 'active',
        ]);
        
        // Should be able to evaluate using deck eval purchase
        $result = $this->service->canEvaluate($user->id, null, 50);
        
        $this->assertTrue($result['allowed']);
        $this->assertEquals($purchase->id, $result['purchase']->id);
        $this->assertEquals('EVAL_100', $result['purchase']->package->code);
        
        // After recording evaluation, purchase should be tracked
        $session = $this->service->getOrCreateSession($user->id, null);
        $this->service->recordEvaluation($session, range(1, 50), $purchase);
        
        $purchase->refresh();
        $this->assertEquals(50, $purchase->cards_used);
        
        // Verify user still exists (no conflict with main membership system)
        $user->refresh();
        $this->assertNotNull($user->id);
    }
}
