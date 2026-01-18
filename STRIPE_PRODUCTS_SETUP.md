# Stripe Products & Prices Configuration Guide

## ⚠️ IMPORTANTE: Leggere SEMPRE la Documentazione Ufficiale

**Prima di implementare qualsiasi funzionalità di Stripe, consultare SEMPRE la documentazione ufficiale:**

- **Subscriptions**: https://stripe.com/docs/billing/subscriptions/overview
- **Setup Intents**: https://stripe.com/docs/payments/setup-intents
- **Payment Intents**: https://stripe.com/docs/payments/payment-intents

### Errori Comuni da Evitare:

1. ❌ **Non assumere** che le subscriptions funzionino come i payment intent one-time
2. ❌ **Non tentare** di creare subscriptions senza prima raccogliere il payment method
3. ❌ **Non usare** `confirmCardPayment` per le subscriptions - usa `confirmCardSetup`
4. ❌ **Non aspettarti** che Stripe crei automaticamente un payment_intent per subscriptions senza payment method

### Flusso Corretto per Subscriptions (da documentazione Stripe):

1. **Setup Intent** → Raccogliere payment method con `stripe.confirmCardSetup()`
2. **Create Subscription** → Creare subscription con il payment method confermato
3. **Invoice** → Stripe addebita automaticamente l'invoice usando il payment method salvato

**Il flusso è DIVERSO dai one-time payments!**

---

## Step 1: Create Products in Stripe Dashboard

### For Advanced Plan:
1. Go to https://dashboard.stripe.com/test/products
2. Click "+ Add product"
3. **Name:** `Advanced Plan`
4. **Description:** `Advanced plan features for BaseCard`
5. **Pricing model:** Recurring
6. Create TWO prices for this product:

#### Advanced Monthly:
- **Price:** `100.00 DKK`
- **Billing period:** Monthly
- **Currency:** DKK
- Copy the **Price ID** (looks like `price_xxxxxxxxxxxxx`)

#### Advanced Yearly:
- **Price:** `1000.00 DKK`  
- **Billing period:** Yearly
- **Currency:** DKK
- Copy the **Price ID** (looks like `price_xxxxxxxxxxxxx`)

### For Premium Plan:
1. Click "+ Add product" again
2. **Name:** `Premium Plan`
3. **Description:** `Premium plan features for BaseCard`
4. **Pricing model:** Recurring
5. Create TWO prices for this product:

#### Premium Monthly:
- **Price:** `150.00 DKK`
- **Billing period:** Monthly
- **Currency:** DKK
- Copy the **Price ID**

#### Premium Yearly:
- **Price:** `1500.00 DKK`
- **Billing period:** Yearly
- **Currency:** DKK
- Copy the **Price ID**

---

## Step 2: Update Database with Price IDs

Run these commands in `php artisan tinker`:

```php
// Update Advanced Plan
$advanced = \App\Models\PricingPlan::where('code', 'advanced')->first();
$advanced->update([
    'stripe_monthly_price_id' => 'price_xxxxxxxxxxxxx', // Replace with your actual ID
    'stripe_yearly_price_id' => 'price_xxxxxxxxxxxxx',  // Replace with your actual ID
]);

// Update Premium Plan
$premium = \App\Models\PricingPlan::where('code', 'premium')->first();
$premium->update([
    'stripe_monthly_price_id' => 'price_xxxxxxxxxxxxx', // Replace with your actual ID
    'stripe_yearly_price_id' => 'price_xxxxxxxxxxxxx',  // Replace with your actual ID
]);

// Verify
\App\Models\PricingPlan::all()->pluck('stripe_monthly_price_id', 'name');
```

---

## Step 3: Verify Configuration

```bash
php artisan tinker
```

```php
// Check all plans have Stripe IDs
\App\Models\PricingPlan::whereNotNull('stripe_monthly_price_id')
    ->whereNotNull('stripe_yearly_price_id')
    ->get(['name', 'stripe_monthly_price_id', 'stripe_yearly_price_id']);

// Should show both plans with their price IDs
```

---

## Important Notes

### Test vs Production
- Use **test mode** products/prices during development
- Create **separate** products/prices for production
- Test Price IDs start with `price_test_`
- Live Price IDs start with `price_`

### Price Structure
Current prices in database (for one-time payments):
- Advanced Monthly: 1000 DKK (10000 cents)
- Advanced Yearly: 10000 DKK (1000000 cents)
- Premium Monthly: 1500 DKK (15000 cents)
- Premium Yearly: 15000 DKK (1500000 cents)

Make sure Stripe prices match these amounts!

### What Each Price ID Is For:
- `stripe_monthly_price_id` → Used for **recurring subscriptions** (monthly)
- `stripe_yearly_price_id` → Used for **recurring subscriptions** (yearly)
- `monthly_price_cents` → Used for **one-time payments** (monthly access)
- `yearly_price_cents` → Used for **one-time payments** (yearly access)

---

## Troubleshooting

### Can't find the plan in database?
```php
// List all plans
\App\Models\PricingPlan::all(['id', 'name', 'code']);
```

### Need to create plans?
```php
\App\Models\PricingPlan::create([
    'name' => 'Advanced',
    'code' => 'advanced',
    'monthly_price_cents' => 10000, // 100 DKK
    'yearly_price_cents' => 100000, // 1000 DKK
    'stripe_monthly_price_id' => 'price_xxxxxxxxxxxxx',
    'stripe_yearly_price_id' => 'price_xxxxxxxxxxxxx',
    'currency' => 'DKK',
]);
```

### Wrong Price ID format?
Price IDs should look like:
- ✅ `price_1ABcDefGHijKLMno`
- ❌ `prod_1ABcDefGHijKLMno` (this is product ID, not price ID)

---

## Next Steps

After completing this configuration:
1. ✅ Products created in Stripe
2. ✅ Prices created (monthly & yearly for each)
3. ✅ Price IDs saved in database
4. ➡️ Ready to implement checkout with subscription support

---

## Quick Reference

| Plan | Period | Amount | Use |
|------|--------|--------|-----|
| Advanced | Monthly | 100 DKK | `stripe_monthly_price_id` |
| Advanced | Yearly | 1000 DKK | `stripe_yearly_price_id` |
| Premium | Monthly | 150 DKK | `stripe_monthly_price_id` |
| Premium | Yearly | 1500 DKK | `stripe_yearly_price_id` |

**Reminder:** These are Stripe Price IDs, NOT amounts. Store the actual `price_xxxxx` string from Stripe.
