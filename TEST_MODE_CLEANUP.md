# ⚠️ TEST MODE - Cleanup Required

## Quick Plan Switcher (Testing Feature)

**Status:** TEMPORARY - Must be removed before production deployment

**Purpose:** This feature was added solely for testing subscription tier switching without payment integration.

---

## Files to Clean Up

### 1. View: `resources/views/profile/subscription.blade.php`

**Remove lines ~98-120** (the yellow test mode box):

```blade
<!-- TEST: Quick Plan Switcher -->
<div class="mt-6 p-4 bg-yellow-900/20 border border-yellow-500/50 rounded-lg">
    <p class="text-yellow-400 text-xs font-semibold mb-3">⚠️ TEST MODE - Quick Plan Switcher</p>
    <form action="{{ route('profile.test-switch-plan') }}" method="POST" class="flex gap-3 items-end">
        @csrf
        <div class="flex-1">
            <label class="block text-gray-400 text-sm mb-1">Select Plan:</label>
            <select name="plan_id" class="w-full bg-[#0d0d0c] border border-white/10 rounded-lg px-3 py-2 text-white">
                @foreach(\App\Models\PricingPlan::all() as $plan)
                    <option value="{{ $plan->id }}" {{ $membershipStatus['tier'] === strtolower($plan->name) ? 'selected' : '' }}>
                        {{ $plan->name }} (€{{ number_format($plan->monthly_price_cents / 100, 2) }}/month)
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition font-medium">
            Activate
        </button>
    </form>
</div>
```

### 2. Controller: `app/Http/Controllers/ProfileController.php`

**Remove method ~164-194** (testSwitchPlan):

```php
/**
 * TEST ONLY: Quick switch pricing plan
 */
public function testSwitchPlan(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'plan_id' => 'required|exists:pricing_plans,id',
    ]);

    $user = $request->user();
    
    // Get or create organization if needed
    if (!$user->organization) {
        $org = \App\Models\Organization::create([
            'name' => $user->name . "'s Organization",
            'code' => 'ORG-' . strtoupper(\Str::random(6)),
            'slug' => \Str::slug($user->name) . '-' . time(),
        ]);
        $user->organization_id = $org->id;
        $user->save();
    } else {
        $org = $user->organization;
    }

    // Update organization with new plan
    $org->pricing_plan_id = $validated['plan_id'];
    $org->billing_period = 'monthly';
    $org->subscription_date = now();
    $org->renew_date = now()->addMonth();
    $org->subscription_cancelled = 0;
    $org->save();

    return redirect()
        ->route('profile.subscription')
        ->with('success', 'Plan activated successfully! (TEST MODE)');
}
```

### 3. Routes: `routes/web.php`

**Remove line ~70** (test-switch-plan route):

```php
// TEST ONLY: Quick plan switcher
Route::post('/profile/test-switch-plan', [ProfileController::class, 'testSwitchPlan'])->name('profile.test-switch-plan');
```

---

## When to Remove

✅ **Remove BEFORE:**
- Production deployment
- Payment gateway integration testing
- UAT (User Acceptance Testing)

⚠️ **Keep DURING:**
- Local development
- Feature testing (subscription tiers, UI changes, access control)
- Integration with payment flows

---

## Replacement

Once removed, plan changes should only be possible through:
- Proper payment gateway (Stripe/PayPal)
- `/billing` routes (existing BillingController)
- Admin panel (if implemented)

---

## Quick Cleanup Commands

```bash
# 1. Check for test mode references
grep -r "test-switch-plan" app/ resources/ routes/

# 2. Check for TEST MODE comments
grep -r "TEST MODE" resources/views/

# 3. After manual removal, verify routes
php artisan route:list | grep test

# Expected: No routes with "test" in profile context
```

---

**Date Added:** 2025-12-29  
**Reason:** Quick testing of subscription tier features without payment integration  
**Delete this file after cleanup is complete.**
