<?php

namespace App\Http\Controllers;

use App\Models\DeckEvaluationPackage;
use App\Models\DeckEvaluationPurchase;
use App\Services\DeckEvaluationEntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DeckEvaluationPurchaseController extends Controller
{
    private DeckEvaluationEntitlementService $entitlementService;

    public function __construct(DeckEvaluationEntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Show available packages
     */
    public function index(Request $request)
    {
        $packages = DeckEvaluationPackage::active()->orderBy('price_cents')->get();
        
        $userId = Auth::id();
        $guestToken = $request->cookie('deck_eval_guest_token') ?? $request->session()->get('deck_eval_guest_token');
        
        $activePurchase = $this->entitlementService->getActivePurchase($userId, $guestToken);
        $summary = $this->entitlementService->getEntitlementSummary($userId, $guestToken);

        return view('deck_evaluation.packages.index', compact('packages', 'activePurchase', 'summary'));
    }

    /**
     * Show purchase form for a specific package
     */
    public function show(Request $request, DeckEvaluationPackage $package)
    {
        if (!$package->is_active) {
            abort(404);
        }

        $userId = Auth::id();
        $guestToken = $request->cookie('deck_eval_guest_token') ?? $request->session()->get('deck_eval_guest_token');

        return view('deck_evaluation.packages.show', compact('package', 'userId', 'guestToken'));
    }

    /**
     * Process purchase (simplified - integrate with your payment provider)
     */
    public function purchase(Request $request, DeckEvaluationPackage $package)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
        ]);

        if (!$package->is_active) {
            return back()->withErrors(['package' => __('deck_evaluation.purchase.package_not_available')]);
        }

        $userId = Auth::id();
        $guestToken = $request->cookie('deck_eval_guest_token') 
            ?? $request->session()->get('deck_eval_guest_token')
            ?? \App\Models\DeckEvaluationSession::generateGuestToken();

        // TODO: CRITICAL - Integrate with actual payment provider
        // Required steps:
        // 1. Initialize payment session with Stripe/PayPal/etc.
        // 2. Validate payment completion webhook
        // 3. Only create purchase record after confirmed payment
        // 4. Store payment_reference from provider
        // 5. Handle payment failures gracefully
        // Current: Creates purchase WITHOUT payment validation - DO NOT USE IN PRODUCTION

        $purchase = DeckEvaluationPurchase::create([
            'user_id' => $userId,
            'guest_token' => $userId ? null : $guestToken,
            'package_id' => $package->id,
            'purchased_at' => now(),
            'expires_at' => now()->addDays($package->validity_days),
            'cards_limit' => $package->max_cards,
            'cards_used' => 0,
            'status' => 'active',
            'payment_reference' => $validated['payment_reference'] ?? null,
        ]);

        // Store guest token in cookie if guest
        if (!$userId) {
            $request->session()->put('deck_eval_guest_token', $guestToken);
            cookie()->queue('deck_eval_guest_token', $guestToken, 60 * 24 * 365); // 1 year
        }

        return redirect()
            ->route('deck-evaluation.packages.success', $purchase)
            ->with('success', __('deck_evaluation.purchase.success'));
    }

    /**
     * Show purchase success page
     */
    public function success(Request $request, DeckEvaluationPurchase $purchase)
    {
        // Verify ownership
        $userId = Auth::id();
        $guestToken = $request->cookie('deck_eval_guest_token') ?? $request->session()->get('deck_eval_guest_token');

        if ($purchase->user_id !== $userId && $purchase->guest_token !== $guestToken) {
            abort(403);
        }

        return view('deck_evaluation.packages.success', compact('purchase'));
    }

    /**
     * Claim guest purchases when user registers/logs in
     */
    public function claimGuestPurchases(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $guestToken = $request->input('guest_token') 
            ?? $request->cookie('deck_eval_guest_token')
            ?? $request->session()->get('deck_eval_guest_token');

        if (!$guestToken) {
            return redirect()->back()->with('info', __('deck_evaluation.claim.no_guest_data'));
        }

        $result = $this->entitlementService->claimGuestData(Auth::id(), $guestToken);

        if ($result['sessions_claimed'] > 0 || $result['purchases_claimed'] > 0) {
            return redirect()
                ->route('account.deck-evaluations')
                ->with('success', __('deck_evaluation.claim.success', $result));
        }

        return redirect()
            ->route('account.deck-evaluations')
            ->with('info', __('deck_evaluation.claim.nothing_to_claim'));
    }
}
