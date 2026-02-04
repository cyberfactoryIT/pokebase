# Stripe Recurring Subscriptions - Implementation Guide

## Current Implementation vs Stripe Subscriptions

### 🔴 Current System (One-time Payment)
```
User pays → We set renew_date → Email reminder → User pays again manually
```
**Pros:**
- Simple implementation
- Full control over billing
- No automatic charges

**Cons:**
- Manual payment required each period
- Risk of subscription lapses
- More work for customer support

### 🟢 Stripe Subscriptions (Automatic Recurring)
```
User subscribes → Stripe charges automatically → Webhook updates our DB
```
**Pros:**
- Automatic recurring payments
- Stripe handles payment failures
- Built-in dunning management
- Better customer experience
- Proration for upgrades/downgrades

**Cons:**
- More complex implementation
- Must handle webhooks reliably
- Stripe takes transaction fee per charge

---

## Implementation Steps for Stripe Subscriptions

### 1. Create Stripe Products & Prices

In Stripe Dashboard or via API:

```bash
# Create Products
stripe products create --name="Advanced Plan"
stripe products create --name="Premium Plan"

# Create Prices (recurring)
stripe prices create \
  --product=prod_xxx \
  --unit-amount=10000 \
  --currency=dkk \
  --recurring[interval]=month

stripe prices create \
  --product=prod_xxx \
  --unit-amount=100000 \
  --currency=dkk \
  --recurring[interval]=year
```

Or manually in Stripe Dashboard → Products

### 2. Store Stripe Price IDs in Database

Add columns to `pricing_plans`:
```sql
ALTER TABLE pricing_plans 
ADD COLUMN stripe_monthly_price_id VARCHAR(255),
ADD COLUMN stripe_yearly_price_id VARCHAR(255);
```

Update your plans:
```php
$plan->update([
    'stripe_monthly_price_id' => 'price_xxxxxxxxxxxxx',
    'stripe_yearly_price_id' => 'price_xxxxxxxxxxxxx',
]);
```

### 3. Modify CheckoutController

Replace Payment Intent with Subscription creation:

```php
// Instead of creating Payment Intent
$paymentIntent = $stripe->paymentIntents->create([...]);

// Create or retrieve Customer
$customer = $stripe->customers->create([
    'email' => $user->email,
    'name' => $org->company,
    'metadata' => [
        'organization_id' => $org->id,
        'user_id' => $user->id,
    ],
]);

// Save Stripe Customer ID
$org->stripe_customer_id = $customer->id;
$org->save();

// Create Subscription
$subscription = $stripe->subscriptions->create([
    'customer' => $customer->id,
    'items' => [
        ['price' => $plan->stripe_monthly_price_id], // or yearly
    ],
    'payment_behavior' => 'default_incomplete',
    'payment_settings' => [
        'save_default_payment_method' => 'on_subscription'
    ],
    'expand' => ['latest_invoice.payment_intent'],
    'metadata' => [
        'organization_id' => $org->id,
        'plan_id' => $plan->id,
    ],
]);

// Save subscription ID
$org->stripe_subscription_id = $subscription->id;
$org->subscription_date = now();
$org->save();

// Return subscription details to frontend
return response()->json([
    'subscription_id' => $subscription->id,
    'client_secret' => $subscription->latest_invoice->payment_intent->client_secret,
]);
```

### 4. Update Frontend JavaScript

```javascript
// After creating subscription on server
const { subscription_id, client_secret } = await response.json();

// Confirm payment with Payment Intent from subscription
const { error } = await stripe.confirmCardPayment(client_secret, {
    payment_method: {
        card: cardElement,
        billing_details: {
            name: document.getElementById('company').value,
            email: document.getElementById('billing_email').value,
            address: {
                line1: document.getElementById('address_line1').value,
                city: document.getElementById('city').value,
                postal_code: document.getElementById('postcode').value,
                country: document.getElementById('country').value,
            }
        }
    }
});

if (error) {
    // Show error
} else {
    // Success - subscription is active
    window.location.href = '/checkout/success?subscription_id=' + subscription_id;
}
```

### 5. Add Stripe Customer ID Column

```sql
ALTER TABLE organizations 
ADD COLUMN stripe_customer_id VARCHAR(255),
ADD COLUMN stripe_subscription_id VARCHAR(255),
ADD INDEX idx_stripe_customer (stripe_customer_id),
ADD INDEX idx_stripe_subscription (stripe_subscription_id);
```

### 6. Implement Webhook Handler

Create route in `routes/api.php`:
```php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
```

Create controller:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle different event types
        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;
            
            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;
            
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
            
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handleInvoicePaymentSucceeded($invoice)
    {
        $subscriptionId = $invoice->subscription;
        
        $org = Organization::where('stripe_subscription_id', $subscriptionId)->first();
        if (!$org) return;

        // Update renew date based on current period end
        $org->renew_date = \Carbon\Carbon::createFromTimestamp($invoice->period_end);
        $org->subscription_cancelled = 0;
        $org->save();

        // Create invoice record
        Invoice::create([
            'organization_id' => $org->id,
            'number' => 'INV-' . $invoice->number,
            'provider' => 'stripe',
            'provider_id' => $invoice->id,
            'currency' => strtoupper($invoice->currency),
            'total_cents' => $invoice->amount_paid,
            'status' => 'paid',
            'issued_at' => now(),
            'paid_at' => now(),
            // ... other fields
        ]);

        \Log::info('Subscription renewed', ['organization_id' => $org->id]);
    }

    private function handleInvoicePaymentFailed($invoice)
    {
        $subscriptionId = $invoice->subscription;
        $org = Organization::where('stripe_subscription_id', $subscriptionId)->first();
        
        if ($org) {
            // Notify admin of failed payment
            $org->users()->each(function($user) {
                $user->notify(new \App\Notifications\PaymentFailedNotification());
            });
        }
    }

    private function handleSubscriptionDeleted($subscription)
    {
        $org = Organization::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($org) {
            $org->subscription_cancelled = 1;
            $org->cancellation_subscription_date = now();
            $org->pricing_plan_id = null; // Or keep until period ends
            $org->save();
        }
    }

    private function handleSubscriptionUpdated($subscription)
    {
        $org = Organization::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($org) {
            // Update subscription status, next billing date, etc.
            $org->renew_date = \Carbon\Carbon::createFromTimestamp($subscription->current_period_end);
            $org->save();
        }
    }
}
```

### 7. Configure Webhook in Stripe

1. Go to Stripe Dashboard → Developers → Webhooks
2. Add endpoint: `https://yourdomain.com/api/stripe/webhook`
3. Select events:
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
   - `customer.subscription.deleted`
   - `customer.subscription.updated`
4. Copy webhook secret to `.env`:
   ```
   STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
   ```

### 8. Update Cancel Subscription Method

```php
public function cancelSubscription()
{
    $org = Auth::user()->organization;
    
    if ($org->stripe_subscription_id) {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        
        // Cancel at period end (not immediately)
        $stripe->subscriptions->update($org->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);
        
        $org->subscription_cancelled = 1;
        $org->cancellation_subscription_date = now();
        $org->save();
    }
    
    return redirect()->route('billing.index')
        ->with('success', 'Subscription will be cancelled at the end of the billing period.');
}
```

---

## Testing Stripe Subscriptions

### Test Cards
```
Success: 4242 4242 4242 4242
Decline: 4000 0000 0000 0002
Requires authentication: 4000 0025 0000 3155
```

### Test Webhooks Locally

Use Stripe CLI:
```bash
# Install Stripe CLI
brew install stripe/stripe-cli/stripe

# Login
stripe login

# Forward webhooks to local
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Trigger test events
stripe trigger invoice.payment_succeeded
```

---

## Migration Path: From One-Time to Recurring

### Option A: Gradual Migration
- Keep current system for existing users
- New subscriptions use Stripe Subscriptions
- Migrate existing users over time

### Option B: Full Migration
- Create Stripe subscriptions for all active users
- Import payment methods (requires customer action)
- Switch cutover date

---

## Cost Comparison

### Current (One-time payments)
- Stripe fee: 1.4% + 1.80 DKK per transaction
- User pays once per period
- Example: 1000 DKK → ~16 DKK fee per transaction

### Stripe Subscriptions (Recurring)
- Same fee: 1.4% + 1.80 DKK per automatic charge
- Charged every month/year automatically
- Example: 1000 DKK/month × 12 = ~192 DKK/year in fees

**No additional cost for using Subscriptions API vs Payment Intents**

---

## Recommendation

### For your use case, I recommend:

**✅ Implement Stripe Subscriptions if:**
- You want truly automatic recurring billing
- You want to reduce churn (users forget to renew)
- You can handle webhook infrastructure reliably
- You want built-in dunning management

**✅ Keep current system if:**
- You prefer manual control over renewals
- You want to avoid webhook complexity
- You need to manually review each renewal
- Your hosting doesn't support reliable webhooks

---

## Implementation Effort

**Stripe Subscriptions:** ~2-3 days
- Update database schema (2 hours)
- Modify checkout flow (4 hours)
- Implement webhook handler (6 hours)
- Testing and bug fixes (4 hours)
- Documentation (2 hours)

**Current system improvements:** ~4 hours
- Add better renewal flow
- Improve reminder emails
- Auto-redirect to checkout before expiry

---

## Questions to Consider

1. **Do you want automatic recurring payments?**
   - Yes → Use Stripe Subscriptions
   - No → Keep current system

2. **Can you handle webhooks reliably?**
   - Yes → Go for it
   - No → Stick with current

3. **Do you need prorated upgrades/downgrades?**
   - Yes → Stripe Subscriptions required
   - No → Current system fine

4. **How important is reducing payment churn?**
   - Critical → Auto-recurring is better
   - Not critical → Manual is fine

---

Would you like me to implement Stripe Subscriptions for automatic recurring payments?
