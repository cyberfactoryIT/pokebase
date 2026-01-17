# ⚠️ Common Errors to Avoid

This document lists common mistakes that should be avoided when working on this Laravel project.

## 🚫 Controller Middleware Errors

### ❌ NEVER DO THIS:
```php
class BillingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // ❌ WRONG in Laravel 12+
    }
}
```

### ✅ CORRECT APPROACH:
**Option 1: Define middleware in routes (PREFERRED)**
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
});
```

**Option 2: Remove constructor entirely**
```php
class BillingController extends Controller
{
    // No constructor - middleware defined in routes
}
```

**Why?** In Laravel 11+, the `$this->middleware()` method was removed from controllers. Middleware should be defined in routes or using PHP attributes.

---

## 🚫 Blade Template Errors

### ❌ NEVER use `@can` without Auth::check()
```blade
@can('seePrices')
    <!-- This will fail if user is not authenticated -->
@endcan
```

### ✅ CORRECT:
```blade
@auth
    @can('seePrices')
        <!-- Safe to check ability -->
    @endcan
@endauth
```

---

## 🚫 Model Relationship Errors

### ❌ Assuming relationships always exist
```php
$card->group->group_id // ❌ Can cause "Trying to get property of non-object"
```

### ✅ CORRECT:
```php
$card->group?->group_id // ✅ Null-safe operator
// OR
optional($card->group)->group_id
// OR with fallback
$card->group->group_id ?? 'default-value'
```

---

## 🚫 User Organization Pattern

### ❌ Assuming user always has organization
```php
$org = Auth::user()->organization;
if (!$org) {
    abort(403); // ❌ Blocks user unnecessarily
}
```

### ✅ CORRECT: Auto-create if needed
```php
if (!$user->organization) {
    $org = \App\Models\Organization::create([
        'name' => $user->name . "'s Organization",
        'code' => 'ORG-' . strtoupper(\Illuminate\Support\Str::random(6)),
        'slug' => \Illuminate\Support\Str::slug($user->name) . '-' . time(),
    ]);
    $user->organization_id = $org->id;
    $user->save();
}
```

---

## 🚫 Translation Errors

### ❌ Using non-existent translation keys
```blade
{{ __('messages.some_key') }} <!-- Returns "messages.some_key" if not found -->
```

### ✅ CORRECT: Provide fallback
```blade
{{ __('messages.some_key', [], 'Default Text') }}
```

---

## 🚫 Route Definition Errors

### ❌ Using closures for complex logic
```php
Route::get('/complex', function() {
    // 50 lines of code ❌
});
```

### ✅ CORRECT: Use controller
```php
Route::get('/complex', [ComplexController::class, 'index']);
```

---

## 🚫 Authentication Check Errors

### ❌ Using methods on null user
```php
Auth::user()->canCreateAnotherDeck(); // ❌ Fails if not authenticated
```

### ✅ CORRECT:
```php
Auth::check() && Auth::user()->canCreateAnotherDeck()
// OR
Auth::user()?->canCreateAnotherDeck()
```

---

## 🚫 Alpine.js / Livewire Conflicts

### ❌ Using Livewire and Alpine.js on same element
```blade
<div x-data="{}" wire:click="method"> ❌
```

### ✅ Choose one approach per feature

---

## 🚫 Mass Assignment Errors

### ❌ Not protecting fillable fields
```php
$user->update($request->all()); // ❌ Security risk
```

### ✅ CORRECT:
```php
$user->update($request->only(['name', 'email']));
// OR validate first
$validated = $request->validate([...]);
$user->update($validated);
```

---

## 🚫 N+1 Query Problems

### ❌ Loading relationships in loop
```php
foreach ($cards as $card) {
    echo $card->group->name; // ❌ N+1 queries
}
```

### ✅ CORRECT: Eager loading
```php
$cards = Card::with('group')->get();
foreach ($cards as $card) {
    echo $card->group->name; // ✅ Single query
}
```

---

## 🚫 Database Transaction Errors

### ❌ Not using transactions for multi-step operations
```php
$user->update([...]);
$org->update([...]);
$invoice->create([...]); // ❌ If this fails, previous updates persist
```

### ✅ CORRECT:
```php
DB::transaction(function () use ($user, $org, $data) {
    $user->update([...]);
    $org->update([...]);
    $invoice->create([...]);
});
```

---

## 🚫 Configuration Errors

### ❌ Using deprecated config values
```php
if (!config('organizations.enabled')) {
    abort(404); // ❌ Blocks features unnecessarily
}
```

### ✅ CORRECT: Make features work regardless
```php
// Allow users without organizations to use features
// Create organization on-demand if needed
```

---

## 📝 Quick Checklist Before Committing

- [ ] No `$this->middleware()` in controller constructors
- [ ] All Auth::user() calls are null-safe
- [ ] All relationship accesses use null-safe operator or checks
- [ ] Translations have fallback text
- [ ] Middleware defined in routes, not controllers
- [ ] No N+1 queries in loops
- [ ] Mass assignment uses `only()` or `validated()`
- [ ] Complex operations use DB transactions

---

## 🔍 How to Use This Document

1. **Before coding**: Review relevant sections
2. **During code review**: Check against this list
3. **When debugging**: Look for these patterns
4. **Update this file**: When you encounter new common errors

---

*Last updated: January 17, 2026*
