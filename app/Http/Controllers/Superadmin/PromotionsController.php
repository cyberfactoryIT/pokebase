<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion; 

class PromotionsController extends Controller
{
    public function index()
    {
        $promotions = \App\Models\Promotion::paginate(20);
        return view('superadmin.promotions.index', compact('promotions'));
    }
    public function create()
    {
        return view('superadmin.promotions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:promotions,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'active' => 'boolean',
            'max_redemptions' => 'nullable|integer',
            'per_org_limit' => 'nullable|integer',
            'new_orgs_only' => 'boolean',
            'stackable' => 'boolean',
            'meta' => 'nullable|json',
        ]);
    $data['active'] = $request->has('active');
    $data['new_orgs_only'] = $request->has('new_orgs_only');
    $data['stackable'] = $request->has('stackable');
    $promotion = Promotion::create($data);
        return redirect()->route('superadmin.promotions.index')->with('success', 'Promotion created!');
    }

    public function edit($promotion)
    {
        $promotion = Promotion::findOrFail($promotion);
        return view('superadmin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, $promotion)
    {
        $promotion = Promotion::findOrFail($promotion);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:promotions,code,' . $promotion->id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'active' => 'boolean',
            'max_redemptions' => 'nullable|integer',
            'per_org_limit' => 'nullable|integer',
            'new_orgs_only' => 'boolean',
            'stackable' => 'boolean',
            'meta' => 'nullable|json',
        ]);
    $data['active'] = $request->has('active');
    $data['new_orgs_only'] = $request->has('new_orgs_only');
    $data['stackable'] = $request->has('stackable');
    $promotion->update($data);
        return redirect()->route('superadmin.promotions.index')->with('success', 'Promotion updated!');
    }

    public function destroy($promotion)
    {
        // Cancella la promozione (aggiungi il modello Promotion se serve)
        // $promotion = Promotion::findOrFail($promotion);
        $promotion = Promotion::findOrFail($promotion);
        $promotion->delete();
        return redirect()->route('superadmin.promotions.index')->with('success', 'Promotion deleted!');
    }
}
