# ⚠️ Common Errors to Avoid

This document lists common mistakes that should be avoided when working on this Laravel project.

## 🚫 AJAX/JSON vs Redirect Response Mismatch

### ❌ Returning redirect when frontend expects JSON
```php
// Controller
public function processPayment(Request $request)
{
    // ... process payment ...
    
    // ❌ WRONG: Frontend is using fetch() expecting JSON response
    return Redirect::route('success')->with('message', 'Done!');
}
```

```javascript
// Frontend
fetch('/process-payment', {
    method: 'POST',
    body: JSON.stringify(data)
})
.then(response => response.json()) // ❌ Expects JSON but gets redirect HTML
.then(data => {
    window.location.href = '/success?id=' + data.invoice_id; // ❌ data.invoice_id is undefined
});
```

### ✅ CORRECT: Match response type to request type
```php
// Controller - Return JSON for AJAX requests
public function processPayment(Request $request)
{
    // ... process payment ...
    
    // ✅ Return JSON with data for frontend redirect
    return response()->json([
        'success' => true,
        'invoice_id' => $invoice->id,
        'message' => 'Payment processed successfully!'
    ]);
}
```

```javascript
// Frontend - Handle JSON response and redirect client-side
fetch('/process-payment', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(data)
})
.then(response => response.json()) // ✅ Receives JSON
.then(data => {
    if (data.success) {
        window.location.href = '/success?invoice_id=' + data.invoice_id; // ✅ Works!
    } else {
        showError(data.error);
    }
});
```

### 🎯 Quick Decision Rule:
- **Form submit** (traditional) → Use `return Redirect::route()`
- **fetch()/AJAX** (JavaScript) → Use `return response()->json()`
- **Check request type**: `$request->expectsJson()` or `$request->wantsJson()`

---

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

### ❌ Using route() helper without verifying route exists
```blade
<a href="{{ route('invoice.download', $id) }}">Download</a>
{{-- ❌ RouteNotFoundException if route doesn't exist --}}
```

### ✅ CORRECT: Check routes file first
```bash
# Verify route exists before using it
php artisan route:list | grep invoice
```

```blade
{{-- Option 1: Verify route exists in routes/web.php FIRST --}}
<a href="{{ route('invoice.download', $id) }}">Download</a>

{{-- Option 2: Use url() with fallback check --}}
@if(Route::has('invoice.download'))
<a href="{{ route('invoice.download', $id) }}">Download</a>
@endif

{{-- Option 3: Comment out until implemented --}}
{{-- TODO: Implement download route
<a href="{{ route('invoice.download', $id) }}">Download</a>
--}}
```

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

## 🚫 Model Field and Relationship Name Errors

### ❌ Assuming field/relationship names without checking the model
```php
// In Controller
$invoice = Invoice::find($id);

// In View
{{ $invoice->lineItems->first()->description }} // ❌ Assumes 'lineItems' exists
{{ $invoice->total_amount }} // ❌ Assumes 'total_amount' field exists
```

### ✅ CORRECT: Always check the model first
```php
// 1. Check the Model FIRST
class Invoice extends Model
{
    protected $fillable = ['total_cents', 'subtotal_cents', ...]; // ✅ Field is 'total_cents'
    
    public function items() { // ✅ Relationship is 'items'
        return $this->hasMany(InvoiceItem::class);
    }
}

// 2. Use eager loading in Controller
$invoice = Invoice::with('items')->find($id); // ✅ Load relationship

// 3. Use correct names in View
{{ $invoice->items->first()->description }} // ✅ Correct relationship name
{{ $invoice->total_cents }} // ✅ Correct field name
```

### 🔍 Pre-Implementation Checklist:
**Before writing controller/view code:**

1. ✅ **Open the Model file** - Check exact field names in `$fillable` or migration
2. ✅ **Check relationship method names** - Don't assume `lineItems`, check if it's `items`
3. ✅ **Use eager loading** - Always load relationships with `::with()` before using them
4. ✅ **Check field suffixes** - Laravel convention: `_cents` for money, `_at` for timestamps
5. ✅ **Test with actual data** - Don't assume structure matches your mental model

### 💡 Quick Model Inspection Commands:
```bash
# View model structure
php artisan tinker
>>> \App\Models\Invoice::first()->toArray() # See actual field names
>>> (new \App\Models\Invoice)->items() # Check relationship exists
```

### 🎯 Real Example from Checkout:
```php
// ❌ WRONG (Assumptions)
$invoice = Invoice::find($id);
$invoice->lineItems->first() // RelationNotFoundException
$invoice->total_amount // Undefined property

// ✅ CORRECT (Verified)
$invoice = Invoice::with('items')->find($id); // Checked Model first
$invoice->items->first() // ✅ Relationship name verified
$invoice->total_cents // ✅ Field name verified from $fillable
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

- [ ] **Matched response type to request** - Return JSON for AJAX/fetch, Redirect for form submits
- [ ] **Verified model field names** - Checked `$fillable` or migration for exact field names
- [ ] **Verified relationship names** - Opened model file to confirm relationship method names
- [ ] **Added eager loading** - Used `::with('relation')` when accessing relationships
- [ ] **Verified route names exist** - Checked routes file or ran `php artisan route:list` before using `route()` helper
- [ ] No `$this->middleware()` in controller constructors
- [ ] All Auth::user() calls are null-safe
- [ ] All relationship accesses use null-safe operator or checks
- [ ] Translations have fallback text
- [ ] Middleware defined in routes, not controllers
- [ ] No N+1 queries in loops
- [ ] Mass assignment uses `only()` or `validated()`
- [ ] Complex operations use DB transactions

---

## � Translation/Cache Issues

### ❌ WRONG: Translation files in `lang/` directory (root)
```bash
# ❌ WRONG - Files in project root lang/ directory
lang/
  ├── en/
  │   └── catalog.php
  └── da/
      └── catalog.php
```

**PROBLEM**: Laravel expects translation files in `resources/lang/`, NOT in root `lang/` directory!

### ✅ CORRECT: Translation files MUST be in `resources/lang/`
```bash
# ✅ CORRECT - Files in resources/lang/ directory
resources/
  └── lang/
      ├── en/
      │   └── catalog.php
      ├── da/
      │   └── catalog.php
      └── it/
          └── catalog.php
```

### 🔧 Quick Fix:
```bash
# Move files from wrong location to correct location:
mv lang/en/catalog.php resources/lang/en/
mv lang/da/catalog.php resources/lang/da/

# Then clear caches:
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

---

### ❌ Translation keys not working after adding new translations
```php
// Created new translation file: resources/lang/da/billing.php
return [
    'title' => 'Fakturering',
];
```

```blade
{{-- View shows English fallback instead of Danish --}}
{{ __('billing.title') }} {{-- Shows "Billing" instead of "Fakturering" --}}
```

### ✅ SOLUTION: Always clear Laravel caches after translation changes
```bash
# Run these THREE commands together after adding/modifying translations:
php artisan config:clear    # Clear config cache
php artisan cache:clear     # Clear application cache
php artisan view:clear      # Clear compiled views

# Or use shortcut:
php artisan optimize:clear  # Clears ALL caches at once
```

### 🎯 When to clear cache:
- ✅ After adding new translation files
- ✅ After modifying existing translation files
- ✅ After changing .env variables
- ✅ After updating config files
- ✅ When translations show fallback text unexpectedly
- ✅ After pulling changes from git that include translation updates

### 💡 Pro Tip: Add to your workflow
```bash
# Always run after translations work:
php artisan optimize:clear && echo "✅ Caches cleared!"

# Or create git hook to auto-clear cache after pull:
# .git/hooks/post-merge
#!/bin/bash
php artisan optimize:clear
```

---

## � CurrencyService Null Handling

### ❌ Type error when user has no preferred_currency
```php
// Service expects non-null string
public static function getSymbol(string $currency): string
{
    return match($currency) {
        'USD' => '$',
        'EUR' => '€',
        // ...
    };
}

// View where user->preferred_currency can be null
{{ CurrencyService::getSymbol($user->preferred_currency) }} 
// ❌ TypeError: must be string, null given
```

### ✅ SOLUTION: Make parameter nullable with default fallback
```php
public static function getSymbol(?string $currency): string
{
    // Handle null case with default
    if ($currency === null) {
        return '€'; // Default to EUR
    }
    
    return match($currency) {
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        default => '€',
    };
}
```

### 🎯 When to use nullable types:
- ✅ User preferences that might not be set
- ✅ Optional database fields
- ✅ API responses that might omit fields
- ✅ Form inputs with default values

---

## 🚫 TCGDEX Backend: Wrong Database Fields

### ❌ Using non-existent fields from TCGCSV in TCGDEX queries
```php
// Trying to use TCGCSV field names on TCGDEX table
$card = TcgdxCard::where('visible_lookup_key', $cardId)->first();
// ❌ SQLSTATE[42S22]: Column not found: Unknown column 'visible_lookup_key'
```

### ✅ SOLUTION: Use correct TCGDEX field names
```php
// TCGDEX uses different field structure
$card = TcgdxCard::where('tcgdex_id', $cardId)->firstOrFail();
// ✅ Uses correct field: tcgdex_id (format: "base1-1", "me02-001")
```

### 📋 Field Mapping Reference:
| TCGCSV | TCGDEX | Notes |
|--------|--------|-------|
| `visible_lookup_key` | `tcgdex_id` | Primary identifier |
| `name` (string) | `name` (JSON) | TCGDEX stores localized names |
| `set_name` | `set.name['en']` | Via relationship |
| N/A | `local_id` | Card number within set |
| `image_url` | `image_large_url` + `/high.webp` | Needs extension |
| `logo_url` (direct) | `logo_url` + `.webp` | Needs extension |

### 🎯 TCGDEX Asset URL Rules:
- **Logos**: Append `.webp` → `https://assets.tcgdex.net/.../logo.webp`
- **Symbols**: Append `.webp` → `https://assets.tcgdex.net/.../symbol.webp`
- **Card Images**: Append `/high.webp` or `/low.webp` for quality

---

## 🚫 Scheduled Tasks Using Wrong PHP Version

### ❌ Cron job fails with PHP version error
```bash
# Crontab entry
0 2 * * * cd /var/www/app/ && ./run-pipeline.sh

# Error in logs:
# Composer detected issues: requires PHP >= 8.4.0, running 7.4.33
```

### ✅ SOLUTION: Use correct PHP binary in scripts
```bash
# In your bash scripts, auto-detect PHP version
#!/bin/bash

# PHP Command - Auto-detect php84 or fall back to php
if command -v php84 &> /dev/null; then
    PHP_CMD="php84"
elif command -v php8.4 &> /dev/null; then
    PHP_CMD="php8.4"
else
    PHP_CMD="php"
fi

# Then use $PHP_CMD instead of php
$PHP_CMD artisan schedule:run
```

### 🎯 Or specify in crontab directly:
```bash
# Use explicit PHP binary path
0 2 * * * cd /var/www/app/ && php84 artisan schedule:run
```

---

## 📝 Documentation Best Practices

### ❌ Creating too many redundant documentation files
```
CATALOG_BACKEND_IMPLEMENTATION.md
PHASE_2_SCHEMA_SUMMARY.md
PHASE_2_COMPLETE.md
IMPLEMENTATION_SUMMARY.md
BUGFIX_CURRENCY_SERVICE.md
FIX_SCHEDULED_PHP_VERSION.md
... (6 new files for one feature!)
```

### ✅ SOLUTION: Consolidate into existing structure
```
PROJECT_STATUS.md        → Add new features here
COMMON_ERRORS.md         → Add new bugs/fixes here
ROADMAP.md              → Add future plans here
DEPRECATION.md          → Add deprecations here
README.md               → Add setup instructions here
```

### 🎯 When to create a new .md file:
- ✅ New major system that needs dedicated guide (e.g., STRIPE_SETUP_GUIDE.md)
- ✅ Complex integration with multiple steps (e.g., INTEGRATION_GUIDE_*.md)
- ❌ Phase completion summaries (update PROJECT_STATUS.md instead)
- ❌ Bug fixes (add to COMMON_ERRORS.md instead)
- ❌ Implementation details (add comments in code or PROJECT_STATUS.md)

---

## 🎨 Blade Component vs Traditional Layout Issue

### ❌ PROBLEM: x-app-layout component not rendering
When using `<x-app-layout>` in Pokemon TCGDEX views, the content was not displaying despite:
- Controller returning data correctly (verified with dd())
- Data being passed to view
- Route working properly

```blade
{{-- ❌ This didn't work in Pokemon catalog views --}}
<x-app-layout>
    <x-slot name="header">
        <h2>Pokemon Sets</h2>
    </x-slot>
    
    <div class="py-12">
        {{-- Content here was not rendering --}}
    </div>
</x-app-layout>
```

### ✅ SOLUTION: Use traditional @extends layout
Replace `<x-app-layout>` with traditional Blade `@extends` directive:

```blade
{{-- ✅ This works correctly --}}
@extends('layouts.app')

@section('content')
<div class="py-12">
    {{-- Content renders properly --}}
</div>
@endsection
```

### 📋 Files affected:
- `resources/views/pokemon/catalog/sets-tcgdex.blade.php`
- `resources/views/pokemon/catalog/set-cards-tcgdex.blade.php`
- `resources/views/pokemon/catalog/card-tcgdex.blade.php`

### 🔍 Debug steps if content not showing:
1. Test with dd() in controller to verify data exists
2. Create minimal HTML test view without layout
3. If HTML test works but layout doesn't → layout component issue
4. Switch to `@extends('layouts.app')` instead of `<x-app-layout>`
5. Clear caches: `php artisan view:clear && php artisan optimize:clear`

### 💡 Rule of thumb:
- **Consistent layout approach**: Use same layout method across similar views
- **Test progressively**: HTML → Layout → Components → Full styling
- **Component issues**: If x-components fail, traditional Blade directives are more reliable

---

### 💡 Rule of thumb:
- **1 feature = 1 section** in existing docs
- **1 major system = 1 new guide** (only if truly complex)
- **Keep root clean**: Max 10-15 .md files in root directory

---

## �🔍 How to Use This Document

1. **Before coding**: Review relevant sections
2. **During code review**: Check against this list
3. **When debugging**: Look for these patterns
4. **Update this file**: When you encounter new common errors

---

*Last updated: January 29, 2026*
