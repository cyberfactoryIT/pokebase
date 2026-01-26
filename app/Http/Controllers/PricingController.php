<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        // Load pricing plans ordered by price (Free, Advanced, Premium)
        $plans = PricingPlan::with('features')
            ->orderBy('monthly_price_cents')
            ->get();
        
        // Ensure we have at least the 3 core plans
        if ($plans->count() < 3) {
            \Log::warning('Pricing page accessed but less than 3 plans found in database', [
                'count' => $plans->count(),
                'existing_codes' => $plans->pluck('code')->toArray()
            ]);
        }
        
        return view('pages.pricing', compact('plans'));
    }
}
