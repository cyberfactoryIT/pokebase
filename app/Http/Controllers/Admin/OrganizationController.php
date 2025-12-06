<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Country;


class OrganizationController extends Controller
{
    // Il controllo di accesso è ora gestito dal middleware nelle rotte

    public function __invoke(Request $request): View
    {
        //Log::info('Invoking organization view for user ID: ' . $request->user()->id);
        $organization = $request->user()->organization;
        return view('admin.organization', compact('organization'));
    }

    public function edit()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(Auth::user()->organization_id);
        if (!Auth::user()->hasRole('admin')) abort(403);
        $user = Auth::user();
        $roles = $user->roles->pluck('name')->toArray();
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $organization = $user->organization;
        $denmark = Country::where('name_en', 'Denmark')->first();
        $otherCountries = Country::where('name_en', '!=', 'Denmark')->orderBy('name_en')->get();
        $countries = collect([]);
        if ($denmark) $countries->push($denmark);
        $countries = $countries->merge($otherCountries);
        return view('admin.organization.edit', compact('organization', 'countries'));
    }

    public function update(Request $request)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(Auth::user()->organization_id);
        if (!Auth::user()->hasRole('admin')) abort(403);
        //log::info('Update request received for organization ID: ' . Auth::user()->organization_id);
        $organization = Auth::user()->organization;
        $request->validate([
            //'cvr' => ['nullable', 'string', 'max:50'],
            //'admin_email' => ['nullable', 'email', 'max:255'],
            //'address' => ['nullable', 'string', 'max:2000'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
        ]);
        $organization->update($request->only([
            //'cvr', 'admin_email', 'address',
            'timezone',
            'company', 'billing_email', 'vat_number',
            'address_line1', 'address_line2', 'city', 'postcode', 'country'
        ]));
        ActivityLog::logActivity('organization', 'update', ['organization' => $organization->name], $organization->id, Auth::id());
        return Redirect::back()->with('status', __('messages.profile_updated'));
    }
}
