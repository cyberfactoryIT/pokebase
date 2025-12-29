# Dual Monetization System - Implementation Guide

## Overview

This system implements TWO parallel monetization streams:

1. **Membership Subscription (Recurring)** - Controls access to catalog, collection, and deck management features
2. **Deck Evaluation Packages (One-shot)** - Controls deck evaluation with usage limits and expiry

These systems are **completely independent** and run in parallel.

## Architecture

### Database Tables

#### Deck Evaluation System Tables

1. `deck_evaluation_packages` - Package definitions (100/600/unlimited)
2. `deck_evaluation_purchases` - One-shot purchases by users or guests
3. `deck_evaluation_sessions` - Evaluation sessions with free 10-card limit
4. `deck_evaluation_runs` - Individual evaluation runs (for idempotency)

#### Existing Membership System Tables

- `pricing_plans` - Membership plans (FREE, ADVANCED, PREMIUM)
- `organizations` - Organization subscriptions
- `invoices` / `invoice_items` - Billing records

### Models

- `DeckEvaluationPackage` - Package definitions
- `DeckEvaluationPurchase` - Purchase records with expiry and card limits
- `DeckEvaluationSession` - Session tracking with guest tokens
- `DeckEvaluationRun` - Individual evaluation runs with deduplication

### Services

**`DeckEvaluationEntitlementService`** - Core business logic:
- `canEvaluate()` - Check if evaluation is allowed
- `recordEvaluation()` - Record evaluation with idempotency
- `claimGuestData()` - Attach guest purchases to registered users
- `getEntitlementSummary()` - Display status
- `markExpiredPurchases()` - Scheduled cleanup

## Business Rules

### Free Evaluation

- **10 cards per session** for guests (no registration required)
- Tracked by `deck_evaluation_sessions.free_cards_used`
- After 10 cards, purchase required

### Paid Packages

#### EVAL_100 (€9.99)
- 100 cards
- Valid for 30 days
- Single deck only

#### EVAL_600 (€49.99)
- 600 cards
- Valid for 30 days
- Single deck only

#### EVAL_UNLIMITED (€99.99)
- Unlimited cards
- Valid for 365 days
- Multiple decks allowed

### Expiry Rules

- Results accessible ONLY while purchase is valid
- Scheduled job marks expired purchases: `php artisan deck-evaluation:mark-expired`
- Add to `app/Console/Kernel.php`:
```php
$schedule->command('deck-evaluation:mark-expired')->daily();
```

### Guest Flow

1. Guest starts evaluation → generates `guest_token` (stored in cookie/session)
2. Guest can evaluate 10 cards free
3. After 10 cards, shown paywall
4. Guest can purchase without registering (purchase linked to `guest_token`)
5. When guest registers/logs in → purchases claimed to user account

### Claiming Process

After login, call:
```php
POST /deck-evaluation/claim-guest-purchases
```

This automatically attaches:
- All `deck_evaluation_sessions` with matching `guest_token`
- All `deck_evaluation_purchases` with matching `guest_token`

## Integration Points

### Controller Updates

**DeckValuationFlowController** - Apply entitlement checks:

```php
use App\Services\DeckEvaluationEntitlementService;

public function step2Submit(Request $request)
{
    $userId = Auth::id();
    $guestToken = $request->cookie('deck_eval_guest_token');
    $items = $request->session()->get('valuation_items', []);
    
    // Check entitlement
    $check = app(DeckEvaluationEntitlementService::class)
        ->canEvaluate($userId, $guestToken, count($items));
    
    if (!$check['allowed']) {
        return redirect()->route('deck-evaluation.packages.index')
            ->with('entitlement_required', $check['reason']);
    }
    
    // Record evaluation
    $result = app(DeckEvaluationEntitlementService::class)
        ->recordEvaluation($check['session'], $cardProductIds, $check['purchase'] ?? null);
    
    // Continue with valuation...
}
```

### Middleware (Optional)

Apply `CheckDeckEvaluationEntitlement` middleware to evaluation routes:

```php
Route::post('/deck-valuation/identity', [DeckValuationFlowController::class, 'step2Submit'])
    ->middleware('check.deck.eval.entitlement');
```

### UI Components

1. **Pricing Page** - Show membership + deck evaluation packages side-by-side
2. **Evaluation Page** - Show progress indicator (X / 10 free or X / 100 remaining)
3. **Account Page** - Show both membership status AND deck evaluation purchases
4. **Paywall** - Trigger when free limit exceeded

## Testing

Run feature tests:
```bash
php artisan test --filter DeckEvaluationEntitlementTest
```

Tests cover:
- ✅ Guest can evaluate up to 10 cards free
- ✅ Guest cannot exceed 10 without purchase
- ✅ Purchase 100 cards allows up to 100 within 30 days, blocks at 101
- ✅ Unlimited allows multiple evaluations for 1 year
- ✅ Expired purchase blocks evaluation
- ✅ Idempotency prevents double-counting
- ✅ Guest data can be claimed by registered user

## Installation Steps

1. **Run migrations:**
```bash
php artisan migrate
```

2. **Seed packages:**
```bash
php artisan db:seed --class=DeckEvaluationPackageSeeder
```

3. **Add scheduled task** to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('deck-evaluation:mark-expired')->daily();
}
```

4. **Update existing DeckValuationFlowController** to use entitlement service

5. **Add routes** to account page:
```php
Route::get('/account/deck-evaluations', function() {
    return view('account.deck-evaluations');
})->middleware('auth')->name('account.deck-evaluations');
```

6. **Guest token handling** - Add to login success:
```php
// After successful login
if ($guestToken = $request->cookie('deck_eval_guest_token')) {
    app(DeckEvaluationEntitlementService::class)->claimGuestData(Auth::id(), $guestToken);
}
```

## Payment Integration

The `DeckEvaluationPurchaseController::purchase()` method has a TODO for payment provider integration:

```php
// TODO: Integrate with actual payment provider here
// 1. Validate payment method
// 2. Create payment intent
// 3. Process payment
// 4. On success, create purchase record
// 5. On failure, return error
```

Integrate with your existing payment provider (Stripe, PayPal, etc.).

## Important Notes

### DO NOT Merge Systems

- Membership subscription ≠ Deck evaluation purchase
- A user can have PREMIUM membership but NO deck evaluation purchase (and vice versa)
- Both systems must run independently
- Check entitlements separately for each feature area

### Membership Controls

- Catalog browsing
- Collection management
- Deck saving/management
- (Other app features)

### Deck Evaluation Controls

- Evaluation beyond 10 cards
- Evaluation results persistence
- Evaluation results availability timeframe

### Data Retention

- Guest sessions expire per package validity
- Purchases remain in DB even after expiry (for history)
- Results accessible only while purchase is valid

## Translations

All strings use i18n keys:
- `resources/lang/*/deck_evaluation.php`
- Keys namespaced: `deck_evaluation.packages.title`, etc.
- Danish/Italian translations provided (review/adjust as needed)

## Security Considerations

- Guest tokens are long random strings (40 chars)
- Tokens stored in secure httpOnly cookies
- Purchase ownership verified before showing results
- Idempotency hash prevents manipulation
- Rate limiting recommended on evaluation endpoints

## Future Enhancements

Consider adding:
- Package bundles (membership + evaluation discount)
- Gift codes/coupons
- Refund handling
- Usage analytics
- Email notifications for expiry warnings
- Auto-renewal options (convert to subscription)
