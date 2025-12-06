<?php
namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;


use App\Models\ActivityLog;

class AdminUserController
{
    // Il controllo del ruolo admin è ora gestito dal middleware nelle rotte

    public function index()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? Auth::user()->organization_id : null
        );
        if (!Auth::user()->hasRole('admin')) abort(403);
    $users = User::where('organization_id', config('organizations.enabled') ? Auth::user()->organization_id : null)->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? Auth::user()->organization_id : null
        );
        if (!Auth::user()->hasRole('admin')) abort(403);
        $roles = Role::whereNotIn('name', ['superadmin'])->get();
        foreach ($roles as $role) {
            $role->name = __('messages.' . $role->name);
        }

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? Auth::user()->organization_id : null
        );
        if (!Auth::user()->hasRole('admin')) abort(403);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'organization_id' => config('organizations.enabled') ? Auth::user()->organization_id : null,
        ]);
        if ($request->filled('role')) {
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
                config('organizations.enabled') ? Auth::user()->organization_id : null
            );
            $user->assignRole($request->role);
        }
    ActivityLog::logActivity('user', 'create', ['user' => $user->name], config('organizations.enabled') ? Auth::user()->organization_id : null, Auth::id());

        return Redirect::route('users.index')->with('status', __('messages.user_created'));
    }

    public function edit(User $user)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? Auth::user()->organization_id : null
        );
        if (!Auth::user()->hasRole('admin')) abort(403);
        $this->authorizeUser($user);
        $roles = $user->roles->pluck('name')->toArray();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? Auth::user()->organization_id : null
        );
        if (!Auth::user()->hasRole('admin')) abort(403);
        $this->authorizeUser($user);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'roles' => ['array'],
        ]);
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        if ($request->has('roles')) {
            app(PermissionRegistrar::class)->setPermissionsTeamId(
                config('organizations.enabled') ? $user->organization_id : null
            );
            $user->syncRoles($request->roles);
        }
        ActivityLog::logActivity('user', 'update', ['user' => $user->name], config('organizations.enabled') ? Auth::user()->organization_id : null, Auth::id());

        return Redirect::route('users.index')->with('status', __('messages.user_updated'));
    }

    public function destroy(User $user)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? Auth::user()->organization_id : null
        );
        if (!Auth::user()->hasRole('admin')) abort(403);
        $this->authorizeUser($user);
         ActivityLog::logActivity('user', 'delete', ['user' => $user->name], config('organizations.enabled') ? Auth::user()->organization_id : null, Auth::id());
       
        $user->delete();
        return Redirect::route('users.index')->with('status', __('messages.user_deleted'));
    }

    protected function authorizeUser(User $user)
    {
    if (config('organizations.enabled') && $user->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }
    }
}
