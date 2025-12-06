<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\EnforcesSuperAdmin;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationsController extends Controller
{
    use EnforcesSuperAdmin;

    

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:organizations,code',
            'admin_email' => 'required|email',
            'address' => 'nullable|string',
            'timezone' => 'nullable|string',
            'subscription_date' => 'nullable|date',
            'renew_date' => 'nullable|date',
            'end_promotion_date' => 'nullable|date',
            'promotion_code' => 'nullable|string',
        ]);
        $organization = Organization::create($data);
        return redirect()->route('superadmin.organizations.index')->with('status', 'Organization created');
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:organizations,code,' . $organization->id,
            'admin_email' => 'required|email',
            'address' => 'nullable|string',
            'timezone' => 'nullable|string',
            'subscription_date' => 'nullable|date',
            'renew_date' => 'nullable|date',
            'end_promotion_date' => 'nullable|date',
            'promotion_code' => 'nullable|string',
        ]);
        $organization->update($data);
        return redirect()->route('superadmin.organizations.index')->with('status', 'Organization updated');
    }

    public function __construct()
    {
        $this->enforceSuperAdmin();
    }

    public function index(Request $request)
    {
        $query = Organization::with('pricingPlan');
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%")
                  ->orWhere('admin_email', 'like', "%$search%");
            });
        }
        $organizations = $query->paginate(20);
        return view('superadmin.organizations.index', compact('organizations', 'search'));
    }
}
