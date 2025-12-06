<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function __construct()
    {
        // Richiede il permesso user.manage (configuralo nelle rotte o tienilo qui)
        $this->middleware(['auth', 'permission:user.manage']);
    }

    /**
     * Lista utenti della stessa organizzazione.
     */
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $users = User::query()
            ->where('organization_id', $orgId)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Form creazione utente.
     */
    public function create(Request $request)
    {
        // Ruoli disponibili (globali, assegnati poi nel team = org)
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name', 'name');

        return view('users.create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Salva un nuovo utente nella stessa organizzazione dell'admin.
     */
    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users,email'],
            'password' => ['required','confirmed', Password::defaults()],
            'roles' => ['nullable','array'],
            'roles.*' => ['string', Rule::exists('roles','name')->where(fn($q) => $q->where('guard_name','web'))],
        ]);

        $user = User::create([
            'organization_id' => $orgId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Imposta il team corrente = org per assegnare ruoli "scopati" sull'org
        app(PermissionRegistrar::class)->setPermissionsTeamId($orgId);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return redirect()->route('users.index')->with('status', __('messages.profile_updated'));
    }

    /**
     * Form modifica utente (stessa org).
     */
    public function edit(Request $request, User $user)
    {
        $orgId = $request->user()->organization_id;
        abort_unless($user->organization_id === $orgId, 403);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name', 'name');

        // Ruoli correnti dell'utente (nomi)
        app(PermissionRegistrar::class)->setPermissionsTeamId($orgId);
        $currentRoles = $user->roles->pluck('name')->all();

        return view('users.edit', [
            'user' => $user,
            'roles' => $roles,
            'currentRoles' => $currentRoles,
        ]);
    }

    /**
     * Aggiorna utente + ruoli (scopo org).
     */
    public function update(Request $request, User $user)
    {
        $orgId = $request->user()->organization_id;
        abort_unless($user->organization_id === $orgId, 403);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','confirmed', Password::defaults()],
            'roles' => ['nullable','array'],
            'roles.*' => ['string', Rule::exists('roles','name')->where(fn($q) => $q->where('guard_name','web'))],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        // Team context = org per sync ruoli
        app(PermissionRegistrar::class)->setPermissionsTeamId($orgId);

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles'] ?? []);
        }

        return redirect()->route('users.index')->with('status', __('messages.profile_updated'));
    }

    /**
     * Elimina utente (stessa org). Non consente di cancellare se stessi.
     */
    public function destroy(Request $request, User $user)
    {
        $orgId = $request->user()->organization_id;
        abort_unless($user->organization_id === $orgId, 403);

        // Evita auto-cancellazione
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => __('messages.cannot_delete_own_account')]);
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', __('messages.deleted_successfully'));
    }
}
