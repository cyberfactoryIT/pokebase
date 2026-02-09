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
        $games = \App\Models\Game::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('auth.register', compact('games'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        \Log::info('Registration attempt started', ['email' => $request->input('email')]);
        
        if(!config('organizations.enabled')) {
            // Use email as unique organization name to prevent duplicates
            $request->merge([
                'organization_name' => $request->input('email'),
                'organization_code' => 'USER_' . strtoupper(substr(md5($request->input('email')), 0, 8)),
                'organization_address' => 'N/A',
                'organization_zipcode' => '00000',
                'organization_city' => 'N/A',
            ]);
        }

        \Log::info('Validating registration data', [
            'has_preferred_game' => $request->has('preferred_game_id'),
            'preferred_game_value' => $request->input('preferred_game_id')
        ]);

        $validated = $request->validate([
            'organization_cvr' => ['nullable', 'string', 'max:20'],
            'organization_name' => ['required', 'string', 'max:191'],
            'organization_code' => ['required', 'string', 'max:191'],
            'organization_address' => ['required', 'string', 'max:255'],
            'organization_zipcode' => ['required', 'string', 'max:20'],
            'organization_city' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'preferred_game_id' => ['required', 'exists:games,id'],
            'trial_code' => ['nullable', 'string', 'max:50'],
            'accept_terms' => ['accepted'],
            'accept_privacy' => ['accepted'],
        ]);
        
        \Log::info('Validation passed', ['validated_keys' => array_keys($validated)]);

        // Validazione composita manuale
        \Log::info('Checking for duplicate organization', [
            'name' => $validated['organization_name'],
            'code' => $validated['organization_code']
        ]);
        
        if (\App\Models\Organization::where('name', $validated['organization_name'])
            ->where('code', $validated['organization_code'])
            ->exists()) {
            \Log::warning('Organization already exists - registration aborted', [
                'name' => $validated['organization_name'],
                'code' => $validated['organization_code']
            ]);
            return back()
                ->withInput()
                ->withErrors(['organization_name' => 'This organization already exists.']);
        }
        
        \Log::info('Organization check passed - unique organization');

        \Log::info('Starting user creation transaction');
        
        $legalAcceptedAt = now('UTC');

        $user = \DB::transaction(function () use ($validated, $request, $legalAcceptedAt) {
            \Log::info('Inside transaction - creating organization');
            
                // 1. Crea organizzazione
                $organization = \App\Models\Organization::create([
                    'name' => $validated['organization_name'],
                    'code' => $validated['organization_code'],
                    'slug' => \Str::slug($validated['organization_code']),
                    'address_line1' => $validated['organization_address'] ?? null,
                    'postcode' => $validated['organization_zipcode'] ?? null,
                    'city' => $validated['organization_city'] ?? null,
                ]);
            

            // 2. Crea utente con token di verifica
            $token = \Str::random(32);
            $expires = now()->addHours(24);
            
            // Use the locale from session (language selector) or app default
            $userLocale = session('locale', config('app.locale', 'da'));
            
            \Log::info('Setting user locale from session', [
                'session_locale' => session('locale'),
                'app_locale' => config('app.locale'),
                'final_locale' => $userLocale
            ]);
            
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Hash::make($validated['password']),
                'organization_id' => $organization ? $organization->id : null,
                'email_verification_token' => $token,
                'email_verification_expires_at' => $expires,
                'default_game_id' => $validated['preferred_game_id'],
                'locale' => $userLocale,
                'terms_accepted_at' => $legalAcceptedAt,
                'terms_version' => config('legal.terms_version'),
                'privacy_accepted_at' => $legalAcceptedAt,
                'privacy_version' => config('legal.privacy_version'),
            ];
            
            \Log::info('Creating user', ['email' => $userData['email']]);
            $user = \App\Models\User::create($userData);
            \Log::info('User created', ['user_id' => $user->id]);
            
            // 2b. Aggiungi il gioco preferito ai giochi attivi dell'utente
            \Log::info('Attaching game to user', ['game_id' => $validated['preferred_game_id']]);
            $user->games()->attach($validated['preferred_game_id'], [
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            \Log::info('Game attached with is_enabled=true');

            $saRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
                $user->organization_id 
            );
            $user->assignRole($saRole);

            \Log::info('Assigning role to user');
            return $user;
        });
        
        \Log::info('Transaction completed', ['user_id' => $user->id]);
        
        // Invio mail di verifica DOPO la transazione
        try {
            \Log::info('Sending verification email to user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => $user->email_verification_token,
                'expires_at' => $user->email_verification_expires_at,
            ]);
            $user->notify(new \App\Notifications\VerifyEmailNotification($user->email_verification_token));
            \Log::info('Verification email sent', ['user_id' => $user->id, 'email' => $user->email]);
        } catch (\Exception $e) {
            \Log::warning('Failed to send verification email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            // Continue anyway - user is registered
        }
        ActivityLog::logActivity(
            'user',
            'create',
            ['user' => $user->name],
            config('organizations.enabled') ? $user->organization_id : null,
            $user->id
        );
        \Log::info('Firing Registered event');
        event(new Registered($user));
        
        \Log::info('Logging in user');
        Auth::login($user);

        \Log::info('Registration process complete, redirecting to dashboard');

        // Claim guest deck evaluation data if exists
        $guestToken = $request->cookie('deck_eval_guest_token');
        if ($guestToken) {
            try {
                $entitlementService = app(\App\Services\DeckEvaluationEntitlementService::class);
                $result = $entitlementService->claimGuestData($user->id, $guestToken);
                
                if ($result['sessions_claimed'] > 0 || $result['purchases_claimed'] > 0) {
                    session()->flash('success', __(
                        'deck_evaluation.claim.success',
                        ['count' => $result['purchases_claimed']]
                    ));
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to claim guest data', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                // Continue anyway
            }
        }
        
        // Apply trial code if provided
        if ($request->filled('trial_code')) {
            try {
                // Normalize code: trim whitespace and convert to uppercase for consistency
                $trialCode = strtoupper(trim($request->input('trial_code')));
                
                $promotionEngine = app(\App\Services\PromotionEngine::class);
                $promotion = $promotionEngine->redeemTrialCode(
                    $trialCode,
                    $user->organization
                );
                
                \Log::info('Trial code redeemed during registration', [
                    'user_id' => $user->id,
                    'organization_id' => $user->organization_id,
                    'code' => $request->input('trial_code'),
                    'promotion_id' => $promotion->id,
                ]);
                
                session()->flash('success', __(
                    'trial.redeemed_success',
                    [
                        'plan' => $promotion->trialPlan->name,
                        'days' => $promotion->trial_duration_days,
                        'expires' => $user->organization->trial_expires_at->format('d/m/Y'),
                    ]
                ));
            } catch (\Exception $e) {
                \Log::warning('Failed to redeem trial code during registration', [
                    'user_id' => $user->id,
                    'code' => $request->input('trial_code'),
                    'error' => $e->getMessage()
                ]);
                // Don't block registration, just show error message
                session()->flash('warning', __('trial.redemption_failed') . ': ' . $e->getMessage());
            }
        }

    return redirect(route('dashboard', [], false));
    }
}
