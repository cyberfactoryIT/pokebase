<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcesAdmin;
use App\Models\Promotion;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PromotionsController extends Controller
{
    use EnforcesAdmin;

    public function __construct()
    {
        $this->enforceAdmin();
    }

    public function index()
    {
        $promotions = Promotion::with('plans')->paginate(15);
        return view('promotions.index', compact('promotions'));
    }
 
    public function create()
    {
        $plans = PricingPlan::all();
        return view('promotions.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32|unique:promotions,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'active' => 'boolean',
            'max_redemptions' => 'nullable|integer',
            'per_org_limit' => 'nullable|integer',
            'new_orgs_only' => 'boolean',
            'stackable' => 'boolean',
            'plans' => 'array',
            'plans.*' => 'exists:pricing_plans,id',
        ]);
        $promo = Promotion::create($data);
        if (!empty($data['plans'])) {
            $promo->plans()->sync($data['plans']);
        }
        return Redirect::route('promotions.index')->with('status', __('messages.promotion_created'));
    }

    public function edit(Promotion $promotion)
    {
        $plans = PricingPlan::all();
        return view('promotions.edit', compact('promotion', 'plans'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:32|unique:promotions,code,' . $promotion->id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'active' => 'boolean',
            'max_redemptions' => 'nullable|integer',
            'per_org_limit' => 'nullable|integer',
            'new_orgs_only' => 'boolean',
            'stackable' => 'boolean',
            'plans' => 'array',
            'plans.*' => 'exists:pricing_plans,id',
        ]);
        $promotion->update($data);
        if (!empty($data['plans'])) {
            $promotion->plans()->sync($data['plans']);
        }
        return Redirect::route('promotions.index')->with('status', __('messages.promotion_updated'));
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return Redirect::route('promotions.index')->with('status', __('messages.promotion_deleted'));
    }
}
