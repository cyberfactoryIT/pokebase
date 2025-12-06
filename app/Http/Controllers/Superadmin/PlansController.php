<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\EnforcesSuperAdmin;
use App\Models\PricingPlan;
use Illuminate\Http\Request;

class PlansController extends Controller
{
    use EnforcesSuperAdmin;

    public function __construct()
    {
        $this->enforceSuperAdmin();
    }

    public function index()
    {
        $plans = PricingPlan::paginate(20);
        return view('superadmin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('superadmin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:pricing_plans,name',
            'code' => 'required|string|alpha_dash|unique:pricing_plans,code',
            'monthly_price_cents' => 'required|integer|min:0',
            'currency' => 'required|string|max:3',
            'yearly_price_cents' => 'nullable|integer|min:0',
        ]);
        $data['yearly_price_cents'] = $data['yearly_price_cents'] ?? ($data['monthly_price_cents'] * 10);
        $plan = PricingPlan::create($data);
        return redirect()->route('superadmin.plans.index')->with('status', 'Plan created');
    }

    public function edit(PricingPlan $plan)
    {
        return view('superadmin.plans.edit', compact('plan'));
    }

    public function update(Request $request, PricingPlan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:pricing_plans,name,' . $plan->id,
            'code' => 'required|string|alpha_dash|unique:pricing_plans,code,' . $plan->id,
            'monthly_price_cents' => 'required|integer|min:0',
            'currency' => 'required|string|max:3',
            'yearly_price_cents' => 'nullable|integer|min:0',
        ]);
        $data['yearly_price_cents'] = $data['yearly_price_cents'] ?? ($data['monthly_price_cents'] * 10);
        $plan->update($data);
        return redirect()->route('superadmin.plans.index')->with('status', 'Plan updated');
    }

    public function destroy(PricingPlan $plan)
    {
        if (config('organizations.enabled') && method_exists($plan, 'organizations') && $plan->organizations()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete: plan is in use.']);
        }
        $plan->delete();
        return redirect()->route('superadmin.plans.index')->with('status', 'Plan deleted');
    }
}
