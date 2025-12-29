# Pre-Deployment Verification Checklist

## ✅ Files Created (29 total)

### Database Layer (5 files)
- [ ] `database/migrations/2025_12_29_000001_create_deck_evaluation_packages_table.php`
- [ ] `database/migrations/2025_12_29_000002_create_deck_evaluation_purchases_table.php`
- [ ] `database/migrations/2025_12_29_000003_create_deck_evaluation_sessions_table.php`
- [ ] `database/migrations/2025_12_29_000004_create_deck_evaluation_runs_table.php`
- [ ] `database/seeders/DeckEvaluationPackageSeeder.php`

### Models (4 files)
- [ ] `app/Models/DeckEvaluationPackage.php`
- [ ] `app/Models/DeckEvaluationPurchase.php`
- [ ] `app/Models/DeckEvaluationSession.php`
- [ ] `app/Models/DeckEvaluationRun.php`

### Services (1 file)
- [ ] `app/Services/DeckEvaluationEntitlementService.php`

### Controllers & Middleware (2 files)
- [ ] `app/Http/Controllers/DeckEvaluationPurchaseController.php`
- [ ] `app/Http/Middleware/CheckDeckEvaluationEntitlement.php`

### Commands (1 file)
- [ ] `app/Console/Commands/MarkExpiredDeckEvaluationPurchases.php`

### Views (2 files)
- [ ] `resources/views/deck_evaluation/packages/index.blade.php`
- [ ] `resources/views/account/deck-evaluations.blade.php`

### Translations (3 files)
- [ ] `resources/lang/en/deck_evaluation.php`
- [ ] `resources/lang/it/deck_evaluation.php`
- [ ] `resources/lang/da/deck_evaluation.php`

### Tests (1 file)
- [ ] `tests/Feature/DeckEvaluationEntitlementTest.php`

### Documentation (5 files)
- [ ] `DECK_EVALUATION_MONETIZATION.md`
- [ ] `IMPLEMENTATION_SUMMARY.md`
- [ ] `INTEGRATION_GUIDE_DeckValuationFlowController.php`
- [ ] `ARCHITECTURE_DIAGRAM.md`
- [ ] `VERIFICATION_CHECKLIST.md` (this file)

### Scripts (1 file)
- [ ] `setup-deck-evaluation.sh` (executable)

### Updated Files (2 files)
- [ ] `app/Models/User.php` (added deck evaluation relationships)
- [ ] `routes/web.php` (added 5 new routes)

---

## 🔧 Installation Steps

### 1. Database Setup
- [ ] Run: `php artisan migrate`
- [ ] Verify: 4 new tables created
  - [ ] `deck_evaluation_packages`
  - [ ] `deck_evaluation_purchases`
  - [ ] `deck_evaluation_sessions`
  - [ ] `deck_evaluation_runs`

### 2. Seed Data
- [ ] Run: `php artisan db:seed --class=DeckEvaluationPackageSeeder`
- [ ] Verify: 3 packages created
  - [ ] EVAL_100 (€9.99)
  - [ ] EVAL_600 (€49.99)
  - [ ] EVAL_UNLIMITED (€99.99)

### 3. Scheduled Task
- [ ] Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('deck-evaluation:mark-expired')->daily();
}
```
- [ ] Verify locally: `php artisan deck-evaluation:mark-expired`

### 4. Integration with Existing Code
- [ ] Update `DeckValuationFlowController` (see `INTEGRATION_GUIDE_DeckValuationFlowController.php`)
  - [ ] Add `DeckEvaluationEntitlementService` dependency
  - [ ] Add entitlement check to `step1Show()`
  - [ ] Add entitlement check to `step2Submit()`
  - [ ] Add entitlement check to `step3Show()`
- [ ] Update `step1.blade.php` view (add entitlement display)

### 5. Login Handler Update
- [ ] Add guest claiming to login success (e.g., `LoginController` or `AuthenticatedSessionController`):
```php
if ($guestToken = $request->cookie('deck_eval_guest_token')) {
    app(\App\Services\DeckEvaluationEntitlementService::class)
        ->claimGuestData(Auth::id(), $guestToken);
}
```

### 6. Routes
- [ ] Add account route in `routes/web.php`:
```php
Route::get('/account/deck-evaluations', function() {
    return view('account.deck-evaluations');
})->middleware('auth')->name('account.deck-evaluations');
```
- [ ] Verify 5 new deck-evaluation routes exist:
  - [ ] `/deck-evaluation/packages`
  - [ ] `/deck-evaluation/packages/{package}`
  - [ ] `/deck-evaluation/packages/{package}/purchase`
  - [ ] `/deck-evaluation/purchases/{purchase}/success`
  - [ ] `/deck-evaluation/claim-guest-purchases`

### 7. Payment Integration
- [ ] Integrate payment provider in `DeckEvaluationPurchaseController::purchase()`
- [ ] Test payment flow end-to-end
- [ ] Verify `payment_reference` is stored

---

## 🧪 Testing

### Run Automated Tests
- [ ] Run: `php artisan test --filter DeckEvaluationEntitlementTest`
- [ ] All 9 tests pass:
  - [ ] `guest_can_evaluate_up_to_10_cards_free`
  - [ ] `guest_cannot_exceed_10_cards_without_purchase`
  - [ ] `guest_blocked_after_using_10_free_cards`
  - [ ] `purchase_100_cards_allows_up_to_100_within_30_days`
  - [ ] `unlimited_package_allows_multiple_evaluations_for_1_year`
  - [ ] `expired_purchase_blocks_evaluation`
  - [ ] `idempotency_prevents_double_counting`
  - [ ] `guest_data_can_be_claimed_by_registered_user`
  - [ ] `entitlement_summary_shows_correct_status`

### Manual Testing Scenarios

#### Test 1: Guest Free Evaluation
- [ ] Visit `/pokemon/deck-valuation` as guest (not logged in)
- [ ] Add 10 cards
- [ ] Verify status shows "10 / 10 free cards used"
- [ ] Try to add 11th card
- [ ] Verify paywall/redirect to packages page

#### Test 2: Guest Purchase Flow
- [ ] As guest (continuing from Test 1)
- [ ] Visit `/deck-evaluation/packages`
- [ ] Click "Select Package" on EVAL_100
- [ ] Complete purchase (or mock if payment not integrated)
- [ ] Verify `deck_evaluation_purchases` record created with `guest_token`
- [ ] Verify cookie `deck_eval_guest_token` is set
- [ ] Return to evaluation and add more cards (should work up to 100)

#### Test 3: Guest Claiming
- [ ] As guest with active purchase (from Test 2)
- [ ] Register a new account
- [ ] Log in
- [ ] Verify purchases show in `/account/deck-evaluations`
- [ ] Verify `deck_evaluation_purchases.user_id` is now set

#### Test 4: Registered User Purchase
- [ ] Log in as existing user
- [ ] Complete evaluation of 10 cards (free tier)
- [ ] Purchase EVAL_600 package
- [ ] Verify can now evaluate up to 600 cards
- [ ] Check `/account/deck-evaluations` shows purchase

#### Test 5: Unlimited Package
- [ ] Purchase EVAL_UNLIMITED as registered user
- [ ] Evaluate 1000 cards (or large number)
- [ ] Verify no card limit enforced
- [ ] Verify expiry is 365 days in future

#### Test 6: Expiry Handling
- [ ] Create purchase with `expires_at` in past (directly in DB)
- [ ] Run: `php artisan deck-evaluation:mark-expired`
- [ ] Verify purchase status changed to 'expired'
- [ ] Try to evaluate with expired purchase
- [ ] Verify blocked and redirected to packages page

#### Test 7: Idempotency
- [ ] Evaluate cards [1, 2, 3, 4, 5] with active purchase
- [ ] Check `cards_used` incremented by 5
- [ ] Re-evaluate same cards [1, 2, 3, 4, 5]
- [ ] Verify `cards_used` did NOT increase again

#### Test 8: Multi-language
- [ ] Switch to Italian: `/language-change` with locale=it
- [ ] Visit `/deck-evaluation/packages`
- [ ] Verify Italian translations display
- [ ] Switch to Danish: locale=da
- [ ] Verify Danish translations display

---

## 🔍 Database Verification

### Check Tables Exist
```sql
SHOW TABLES LIKE 'deck_evaluation_%';
```
Should return 4 tables.

### Check Packages Seeded
```sql
SELECT * FROM deck_evaluation_packages;
```
Should show 3 packages.

### Check Foreign Keys
```sql
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME LIKE 'deck_evaluation_%'
    AND REFERENCED_TABLE_NAME IS NOT NULL;
```
Should show all FK constraints.

---

## 🔒 Security Verification

- [ ] Guest tokens are 40+ characters
- [ ] Guest tokens stored in httpOnly cookies
- [ ] Purchase ownership verified before showing results
- [ ] Status enums prevent invalid states
- [ ] Foreign keys prevent orphaned records
- [ ] Idempotency hash prevents manipulation

---

## 📱 UI Verification

### Package Listing Page
- [ ] Visit `/deck-evaluation/packages`
- [ ] Verify 3 packages displayed
- [ ] Verify pricing formatted correctly (€9.99, €49.99, €99.99)
- [ ] Verify features listed (cards, validity, multiple decks)
- [ ] Click each "Select Package" button works

### Account Page
- [ ] Visit `/account/deck-evaluations` (must be logged in)
- [ ] With no purchases: shows "No purchases yet" message
- [ ] With purchases: shows purchase cards with:
  - [ ] Package name
  - [ ] Status badge (active/expired/consumed)
  - [ ] Purchase date
  - [ ] Expiry date
  - [ ] Cards remaining
  - [ ] Cards used

### Evaluation Flow Integration
- [ ] Visit `/pokemon/deck-valuation`
- [ ] Verify entitlement status shown (if integrated)
- [ ] Add cards and verify counter updates
- [ ] Hit limit and verify paywall triggers

---

## 📚 Documentation Review

- [ ] Read `DECK_EVALUATION_MONETIZATION.md` for complete guide
- [ ] Read `IMPLEMENTATION_SUMMARY.md` for what was built
- [ ] Read `INTEGRATION_GUIDE_DeckValuationFlowController.php` for integration steps
- [ ] Read `ARCHITECTURE_DIAGRAM.md` for system flow understanding

---

## 🚀 Deployment Readiness

### Before Production Deploy:
- [ ] All tests pass
- [ ] Manual testing complete
- [ ] Payment integration tested
- [ ] Translation review complete
- [ ] Security audit passed
- [ ] Database backups configured
- [ ] Scheduled task confirmed in Kernel.php
- [ ] Monitoring/logging configured

### After Deploy:
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed packages: `php artisan db:seed --class=DeckEvaluationPackageSeeder --force`
- [ ] Verify scheduled task runs: check Laravel scheduler logs
- [ ] Monitor purchase creation: check logs for errors
- [ ] Monitor guest claiming: verify users can access their purchases

---

## ⚠️ Critical Rules Verification

- [ ] ✅ Membership subscription does NOT grant deck evaluation
- [ ] ✅ Deck evaluation purchase does NOT grant membership features
- [ ] ✅ Systems run completely independently
- [ ] ✅ Guest can purchase without registration
- [ ] ✅ Guest purchases automatically claimed on login
- [ ] ✅ Free tier limited to 10 cards
- [ ] ✅ Purchased packages enforce limits (100/600/unlimited)
- [ ] ✅ Expiry enforced (30 days / 365 days)
- [ ] ✅ Idempotency prevents double-counting
- [ ] ✅ No hardcoded strings (all i18n)

---

## 🎯 Success Criteria

### Must Have (Critical):
- [ ] All migrations run without errors
- [ ] All tests pass
- [ ] Guest can use 10 free cards
- [ ] Guest blocked after 10 cards
- [ ] Purchase flow works end-to-end
- [ ] Expiry enforced correctly
- [ ] Idempotency works

### Should Have (Important):
- [ ] Payment integration complete
- [ ] Guest claiming works on login
- [ ] Account page shows purchases
- [ ] Scheduled task configured
- [ ] Translations reviewed

### Nice to Have (Optional):
- [ ] Email notifications for expiry
- [ ] Analytics dashboard
- [ ] Refund handling
- [ ] Gift codes/coupons

---

## ✅ Sign-Off

When all checkboxes above are checked, the system is ready for production deployment.

**Date Completed:** _________________

**Verified By:** _________________

**Deployed To Production:** _________________

**Notes:**
_____________________________________________________________________________
_____________________________________________________________________________
_____________________________________________________________________________
