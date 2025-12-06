<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\ActivityLog;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:191'],
            'organization_code' => ['required', 'string', 'max:191'],
            'organization_address' => ['required', 'string', 'max:255'],
            'organization_zipcode' => ['required', 'string', 'max:20'],
            'organization_city' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // Validazione composita manuale
        if (\App\Models\Organization::where('name', $validated['organization_name'])
            ->where('code', $validated['organization_code'])
            ->exists()) {
            return back()
                ->withInput()
                ->withErrors(['organization_name' => 'This organization already exists.']);
        }

        $user = \DB::transaction(function () use ($validated, $request) {
            $organization = null;
            if (config('organizations.enabled')) {
                // 1. Crea organizzazione
                $organization = \App\Models\Organization::create([
                    'name' => $validated['organization_name'],
                    'code' => $validated['organization_code'],
                    'slug' => \Str::slug($validated['organization_code']),
                    'address_line1' => $validated['organization_address'] ?? null,
                    'postcode' => $validated['organization_zipcode'] ?? null,
                    'city' => $validated['organization_city'] ?? null,
                ]);
            }

            // 2. Crea utente con token di verifica
            $token = \Str::random(32);
            $expires = now()->addHours(24);
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Hash::make($validated['password']),
                'organization_id' => $organization ? $organization->id : null,
                'email_verification_token' => $token,
                'email_verification_expires_at' => $expires,
            ]);

            $saRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
                config('organizations.enabled') ? $user->organization_id : null
            );
            $user->assignRole($saRole);

            return $user;
        });
        // Invio mail di verifica DOPO la transazione
        \Log::info('Sending verification email to user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => $user->email_verification_token,
            'expires_at' => $user->email_verification_expires_at,
        ]);
        $user->notify(new \App\Notifications\VerifyEmailNotification($user->email_verification_token));
        \Log::info('Verification email sent', ['user_id' => $user->id, 'email' => $user->email]);
        ActivityLog::logActivity(
            'user',
            'create',
            ['user' => $user->name],
            config('organizations.enabled') ? $user->organization_id : null,
            $user->id
        );
        event(new Registered($user));
        Auth::login($user);

    return redirect(route('dashboard', [], false));
    }
}
