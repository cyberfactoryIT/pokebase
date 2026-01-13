<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        // Load all pricing plans with their features
        $plans = PricingPlan::with('features')->orderBy('monthly_price_cents')->get();
        
        return view('pages.pricing', compact('plans'));
    }
}
