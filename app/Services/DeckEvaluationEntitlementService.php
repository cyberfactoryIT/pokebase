<?php

namespace App\Services;

use App\Models\DeckEvaluationSession;
use App\Models\DeckEvaluationPurchase;
use App\Models\DeckEvaluationRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service to handle deck evaluation entitlements and business rules
 * 
 * This service determines if a user/guest can evaluate cards based on:
 * - Free 10-card limit per session
 * - Active purchases (100/600/unlimited packages)
 * - Expiry dates
 * - Card usage limits
 */
class DeckEvaluationEntitlementService
{
    /**
     * Check if an evaluation is allowed
     * 
     * @param int|null $userId User ID or null for guest
     * @param string|null $guestToken Guest token or null for authenticated user
     * @param int $cardsCount Number of cards to evaluate
     * @return array ['allowed' => bool, 'reason' => string, 'purchase' => Purchase|null, 'session' => Session|null]
     */
    public function canEvaluate(?int $userId, ?string $guestToken, int $cardsCount): array
    {
        // Get or create session
        $session = $this->getOrCreateSession($userId, $guestToken);

        // First check for any purchase (including expired ones)
        $anyPurchase = DeckEvaluationPurchase::forUserOrGuest($userId, $guestToken)
            ->with('package')
            ->orderBy('expires_at', 'desc')
            ->first();

        if ($anyPurchase) {
            // Check if expired first
            if ($anyPurchase->isExpired()) {
                $anyPurchase->markExpired();
                return [
                    'allowed' => false,
                    'reason' => 'purchase_expired',
                    'purchase' => $anyPurchase,
                    'session' => $session,
                ];
            }

            // Check if consumed
            if ($anyPurchase->status === 'consumed') {
                return [
                    'allowed' => false,
                    'reason' => 'insufficient_cards',
                    'purchase' => $anyPurchase,
                    'session' => $session,
                    'remaining' => 0,
                ];
            }

            // Check card limits for active purchases
            if ($anyPurchase->cards_limit !== null) {
                $remaining = $anyPurchase->remaining_cards;
                if ($cardsCount > $remaining) {
                    return [
                        'allowed' => false,
                        'reason' => 'insufficient_cards',
                        'purchase' => $anyPurchase,
                        'session' => $session,
                        'remaining' => $remaining,
                    ];
                }
            }

            return [
                'allowed' => true,
                'reason' => 'active_purchase',
                'purchase' => $anyPurchase,
                'session' => $session,
            ];
        }

        // No purchase - check free limit
        if ($session->hasFreeCardsRemaining()) {
            $remaining = $session->remaining_free_cards;
            
            if ($cardsCount > $remaining) {
                return [
                    'allowed' => false,
                    'reason' => 'free_limit_exceeded',
                    'session' => $session,
                    'remaining' => $remaining,
                ];
            }

            return [
                'allowed' => true,
                'reason' => 'free_evaluation',
                'session' => $session,
                'remaining' => $remaining,
            ];
        }

        // Free limit exhausted and no purchase
        return [
            'allowed' => false,
            'reason' => 'purchase_required',
            'session' => $session,
        ];
    }

    /**
     * Record an evaluation run (with idempotency)
     * 
     * @param DeckEvaluationSession $session
     * @param array $cardProductIds Array of product IDs being evaluated
     * @param DeckEvaluationPurchase|null $purchase
     * @return array ['success' => bool, 'run' => Run|null, 'is_duplicate' => bool]
     */
    public function recordEvaluation(
        DeckEvaluationSession $session,
        array $cardProductIds,
        ?DeckEvaluationPurchase $purchase = null
    ): array {
        $runHash = DeckEvaluationRun::generateRunHash($cardProductIds);
        $cardsCount = count($cardProductIds);

        // Check if this exact evaluation was already run (idempotency)
        $existingRun = DeckEvaluationRun::where('session_id', $session->id)
            ->where('run_hash', $runHash)
            ->first();

        if ($existingRun) {
            return [
                'success' => true,
                'run' => $existingRun,
                'is_duplicate' => true,
            ];
        }

        // Create new run
        DB::beginTransaction();
        try {
            $run = DeckEvaluationRun::create([
                'session_id' => $session->id,
                'purchase_id' => $purchase?->id,
                'run_hash' => $runHash,
                'cards_count' => $cardsCount,
                'evaluated_at' => now(),
            ]);

            // Update usage counters
            if ($purchase) {
                // Paid evaluation - increment purchase usage
                if (!$purchase->incrementCardsUsed($cardsCount)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'error' => 'Failed to increment purchase usage',
                    ];
                }
            } else {
                // Free evaluation - increment session usage
                $session->incrementFreeCardsUsed($cardsCount);
            }

            DB::commit();

            return [
                'success' => true,
                'run' => $run,
                'is_duplicate' => false,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get or create a session for user/guest
     */
    public function getOrCreateSession(?int $userId, ?string $guestToken): DeckEvaluationSession
    {
        $query = DeckEvaluationSession::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($guestToken) {
            $query->where('guest_token', $guestToken);
        }

        $session = $query->where('status', 'draft')->first();

        if (!$session) {
            $session = DeckEvaluationSession::create([
                'user_id' => $userId,
                'guest_token' => $guestToken ?: DeckEvaluationSession::generateGuestToken(),
                'status' => 'draft',
                'free_cards_limit' => 10,
                'free_cards_used' => 0,
            ]);
        }

        return $session;
    }

    /**
     * Get active purchase for user or guest
     */
    public function getActivePurchase(?int $userId, ?string $guestToken): ?DeckEvaluationPurchase
    {
        return DeckEvaluationPurchase::forUserOrGuest($userId, $guestToken)
            ->active()
            ->with('package')
            ->orderBy('expires_at', 'desc')
            ->first();
    }

    /**
     * Get any recent purchase (including consumed) for user or guest
     */
    public function getRecentPurchase(?int $userId, ?string $guestToken): ?DeckEvaluationPurchase
    {
        return DeckEvaluationPurchase::forUserOrGuest($userId, $guestToken)
            ->whereIn('status', ['active', 'consumed'])
            ->where('expires_at', '>', now())
            ->with('package')
            ->orderBy('expires_at', 'desc')
            ->first();
    }

    /**
     * Claim all guest data to a user (when guest registers/logs in)
     */
    public function claimGuestData(int $userId, string $guestToken): array
    {
        DB::beginTransaction();
        try {
            // Claim sessions
            $sessionsCount = DeckEvaluationSession::where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);

            // Claim purchases
            $purchasesCount = DeckEvaluationPurchase::where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);

            DB::commit();

            return [
                'sessions_claimed' => $sessionsCount,
                'purchases_claimed' => $purchasesCount,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get entitlement summary for display
     */
    public function getEntitlementSummary(?int $userId, ?string $guestToken): array
    {
        $session = $this->getOrCreateSession($userId, $guestToken);
        $purchase = $this->getActivePurchase($userId, $guestToken);

        if ($purchase) {
            return [
                'type' => 'purchased',
                'package_name' => $purchase->package->name,
                'package_code' => $purchase->package->code,
                'cards_limit' => $purchase->cards_limit,
                'cards_used' => $purchase->cards_used,
                'cards_remaining' => $purchase->remaining_cards,
                'expires_at' => $purchase->expires_at,
                'is_unlimited' => $purchase->cards_limit === null,
                'allows_multiple_decks' => $purchase->package->allows_multiple_decks,
            ];
        }

        return [
            'type' => 'free',
            'cards_limit' => $session->free_cards_limit,
            'cards_used' => $session->free_cards_used,
            'cards_remaining' => $session->remaining_free_cards,
        ];
    }

    /**
     * Mark expired purchases (should be called by scheduled task)
     */
    public function markExpiredPurchases(): int
    {
        $expired = DeckEvaluationPurchase::needsExpiry()->get();
        
        foreach ($expired as $purchase) {
            $purchase->markExpired();
        }

        return $expired->count();
    }
}
