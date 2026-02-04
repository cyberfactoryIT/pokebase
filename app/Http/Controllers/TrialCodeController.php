<?php

namespace App\Http\Controllers;

use App\Services\PromotionEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrialCodeController extends Controller
{
    protected $promotionEngine;
    
    public function __construct(PromotionEngine $promotionEngine)
    {
        $this->middleware('auth');
        $this->promotionEngine = $promotionEngine;
    }
    
    /**
     * Show trial code redemption form
     */
    public function show()
    {
        $user = Auth::user();
        $org = $user->organization;
        
        return view('trial.redeem', [
            'organization' => $org,
            'isOnTrial' => $org->isOnTrial(),
            'hasSubscription' => $org->stripe_subscription_id !== null,
        ]);
    }
    
    /**
     * Redeem a trial code
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);
        
        $user = Auth::user();
        $org = $user->organization;
        
        try {
            $promotion = $this->promotionEngine->redeemTrialCode(
                $request->input('code'),
                $org
            );
            
            // Refresh organization to get updated trial data
            $org->refresh();
            
            return redirect()->route('dashboard')->with('success', __(
                'trial.redeemed_success',
                [
                    'plan' => $org->trialPlan->name,
                    'days' => $promotion->trial_duration_days,
                    'expires' => $org->trial_expires_at->format('d/m/Y'),
                ]
            ));
            
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['code' => $e->getMessage()]);
        }
    }
}
