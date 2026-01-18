# Dual Payment System Implementation Status

## ✅ Completed (Step 1/9)

### Database Schema
- ✅ `organizations` table: Added `payment_type`, `stripe_customer_id`, `stripe_subscription_id`
- ✅ `pricing_plans` table: Added `stripe_monthly_price_id`, `stripe_yearly_price_id`
- ✅ `invoices` table: Added `payment_type` tracking
- ✅ Models updated with new fillable fields

### Key Design Decisions
- **Two payment types:** `subscription` (recurring) vs `one_time` (single purchase)
- **Backward compatible:** Existing one-time payment code remains functional
- **Flexible:** Can offer both options to customers

---

## 📋 Next Steps (Steps 2-9)

### Step 2: Configure Stripe Products
**Status:** ⏳ Waiting for you to complete
**Guide:** See `STRIPE_PRODUCTS_SETUP.md`
**Action Required:**
1. Create products in Stripe Dashboard
2. Create monthly & yearly prices for each product
3. Run tinker commands to save Price IDs in database

**Estimated time:** 15 minutes

---

### Step 3: Update CheckoutController  
**Status:** ⏳ Next
**Changes needed:**
- Add `payment_type` parameter to checkout
- Keep existing `createPaymentIntent()` for one-time
- Add new `createSubscription()` for recurring
- Update `processPayment()` to handle both types

**Files to modify:**
- `app/Http/Controllers/CheckoutController.php`

---

### Step 4: Update Checkout UI
**Status:** ⏳ Pending
**Changes needed:**
- Add payment type selector (radio buttons or toggle)
- Show different messaging for subscription vs one-time
- Update order summary based on selection

**Files to modify:**
- `resources/views/checkout/index.blade.php`
- Add translations for new UI elements

---

### Step 5: Update Frontend JavaScript
**Status:** ⏳ Pending
**Changes needed:**
- Check `payment_type` selection
- Call correct endpoint (`createPaymentIntent` vs `createSubscription`)
- Handle subscription confirmation flow

**Files to modify:**
- `resources/views/checkout/index.blade.php` (JavaScript section)

---

### Step 6: Create Webhook Handler
**Status:** ⏳ Pending
**New files:**
- `app/Http/Controllers/StripeWebhookController.php`
- Handle `invoice.payment_succeeded` (subscriptions)
- Handle `invoice.payment_failed`
- Handle `customer.subscription.deleted`
- Handle `customer.subscription.updated`

**Routes to add:**
- `POST /api/stripe/webhook`

---

### Step 7: Update Cancel Subscription
**Status:** ⏳ Pending
**Changes needed:**
- Check `payment_type` before canceling
- If subscription: Call Stripe API to cancel
- If one-time: Just update local DB

**Files to modify:**
- `app/Http/Controllers/BillingController.php`

---

### Step 8: Configure Stripe Webhook
**Status:** ⏳ Pending (after Step 6)
**Action Required:**
1. Deploy webhook endpoint
2. Add webhook in Stripe Dashboard
3. Copy webhook secret to `.env`
4. Test with Stripe CLI

---

### Step 9: Testing
**Status:** ⏳ Final step
**Test scenarios:**
- ✅ One-time payment (existing flow)
- ⏳ Subscription creation
- ⏳ Subscription renewal (via webhook)
- ⏳ Subscription cancellation
- ⏳ Payment failure handling
- ⏳ Upgrade/downgrade

---

## Current System Behavior

### One-Time Payment (Existing - Still Works)
```
User → Checkout → Payment Intent → Pay → Success
       ↓
   [payment_type: one_time]
   [renew_date: +1 month/year]
   [reminder email before expiry]
```

### Recurring Subscription (New - To Implement)
```
User → Checkout → Select "Recurring" → Subscription → Pay → Success
       ↓
   [payment_type: subscription]
   [stripe_subscription_id saved]
   [Stripe auto-charges monthly/yearly]
   [Webhook updates renew_date]
```

---

## How to Continue

### Option A: Complete Stripe Setup First
1. **You:** Configure Stripe products (15 min) → See `STRIPE_PRODUCTS_SETUP.md`
2. **Me:** Implement CheckoutController changes (1 hour)
3. **Me:** Update frontend UI & JS (1 hour)
4. **Me:** Create webhook handler (2 hours)
5. **Together:** Test and deploy

**Total: 1 day of work**

### Option B: Implement Core Logic First
1. **Me:** Implement all code assuming Stripe IDs exist
2. **You:** Configure Stripe products later
3. **Risk:** Can't test until Stripe is configured

---

## Questions to Answer

1. **Should users see both options at checkout?**
   - Option A: Show radio buttons "One-time" vs "Recurring subscription"
   - Option B: Only show subscription option (force recurring)
   - Option C: Show subscription by default, but allow one-time on request

2. **Pricing for recurring vs one-time:**
   - Same price for both?
   - Discount for recurring (e.g., 10% off)?
   - Higher price for one-time (pay-as-you-go premium)?

3. **What happens to existing users?**
   - Keep them on one-time (manual renewal)
   - Offer migration to subscription
   - Auto-convert on next renewal?

4. **Priority features:**
   - Immediate need: Recurring subscriptions
   - Can wait: One-time purchases for specific items
   - Both equally important

---

## Recommendation

**Best path forward:**

1. **Today:** You configure Stripe products (use `STRIPE_PRODUCTS_SETUP.md`)
2. **Today:** I implement Steps 3-7 (controller, UI, webhook)
3. **Tomorrow:** We test subscription flow
4. **Tomorrow:** Configure webhook in Stripe and test renewals

This keeps one-time payments working while adding subscription support alongside.

---

## Files Ready for Next Steps

✅ Migration files created and run
✅ Models updated  
✅ Stripe setup guide ready
⏳ Waiting for Stripe Product configuration
⏳ Ready to code CheckoutController updates

**Let me know:**
1. Have you configured Stripe products yet?
2. Which option do you prefer for the checkout UI?
3. Should we continue with implementation?
