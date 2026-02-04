# Stripe Checkout Integration - Setup Guide

## ✅ What's Been Implemented

### 1. Backend Components
- **CheckoutController** (`app/Http/Controllers/CheckoutController.php`)
  - `show()` - Displays checkout page with plan details
  - `createPaymentIntent()` - Creates Stripe Payment Intent
  - `processPayment()` - Processes payment after Stripe confirmation
  - `success()` - Shows success page with invoice
  - `cancel()` - Shows cancellation page

- **Routes** (`routes/web.php`)
  - `GET /checkout` - Checkout page
  - `POST /checkout/create-payment-intent` - Create Payment Intent
  - `POST /checkout/process` - Process payment
  - `GET /checkout/success` - Success page
  - `GET /checkout/cancel` - Cancel page

### 2. Frontend Components
- **Checkout View** (`resources/views/checkout/index.blade.php`)
  - Billing information form (all fields mandatory except VAT)
  - Stripe Elements card input
  - Plan summary with price calculation
  - JavaScript payment flow integration
  
- **Success Page** (`resources/views/checkout/success.blade.php`)
  - Payment confirmation
  - Invoice details display
  - Download invoice button
  
- **Cancel Page** (`resources/views/checkout/cancel.blade.php`)
  - Cancellation message
  - Try again button
  - Support contact info

### 3. Translations
- English (`resources/lang/en/checkout.php`)
- Italian (`resources/lang/it/checkout.php`)
- Danish (`resources/lang/da/checkout.php`)

### 4. Integration Points
- Upgrade buttons in billing page now redirect to checkout
- Billing period (monthly/yearly) dynamically updates checkout URLs
- Downgrade buttons still use old flow (requires confirmation)

---

## 🔧 Environment Configuration

Add these variables to your `.env` file:

```env
# Stripe API Keys (Test Mode)
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

### How to Get Your Stripe Keys:

1. **Login to Stripe Dashboard**
   - Go to: https://dashboard.stripe.com/test/dashboard
   - Make sure you're in **Test Mode** (toggle in the top right)

2. **Get Publishable Key** (`STRIPE_KEY`)
   - Click **Developers** → **API keys**
   - Copy the **Publishable key** (starts with `pk_test_`)
   - Paste it as `STRIPE_KEY` in your `.env`

3. **Get Secret Key** (`STRIPE_SECRET`)
   - On the same page, reveal and copy the **Secret key** (starts with `sk_test_`)
   - Paste it as `STRIPE_SECRET` in your `.env`

4. **Get Webhook Secret** (Optional for now)
   - Click **Developers** → **Webhooks**
   - Click **Add endpoint**
   - Add your URL: `https://basecard.local:8890/webhook/stripe`
   - Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`
   - Copy the **Signing secret** (starts with `whsec_`)
   - Paste it as `STRIPE_WEBHOOK_SECRET` in your `.env`

---

## 🧪 Testing with Stripe Test Cards

Use these test card numbers in the checkout form:

### Successful Payment
- **Card**: `4242 4242 4242 4242`
- **Expiry**: Any future date (e.g., `12/25`)
- **CVC**: Any 3 digits (e.g., `123`)
- **ZIP**: Any 5 digits (e.g., `12345`)

### Payment Requires Authentication (3D Secure)
- **Card**: `4000 0027 6000 3184`

### Declined Payment
- **Card**: `4000 0000 0000 0002`

### Insufficient Funds
- **Card**: `4000 0000 0000 9995`

---

## 🚀 Testing the Flow

1. **Start Your Server**
   ```bash
   php artisan serve --host=basecard.local --port=8890
   ```

2. **Go to Billing Page**
   - Navigate to: https://basecard.local:8890/billing
   - You should see your current plan and available plans

3. **Click "Upgrade to Advanced" or "Upgrade to Premium"**
   - This will take you to the checkout page
   - The plan and billing period (monthly/yearly) are pre-selected

4. **Fill in Billing Information**
   - All fields are mandatory except VAT Number
   - Denmark is the first option in the country dropdown
   - Address fields are validated

5. **Enter Test Card Details**
   - Use `4242 4242 4242 4242` for successful test
   - Enter any future expiry date
   - Enter any 3-digit CVC

6. **Click "Complete Payment"**
   - The button will show "Processing..." with a spinner
   - Stripe will validate the card
   - Payment Intent is created and confirmed
   - Organization subscription is updated
   - Invoice is created
   - You're redirected to success page

7. **Success Page**
   - Shows confirmation message
   - Displays invoice details
   - Offers "Download Invoice" and "Go to Billing" buttons

---

## 🔍 What Happens Behind the Scenes

1. **User clicks Upgrade**
   - Browser: `GET /checkout?plan_id=2&billing_period=monthly`
   - Server: Loads plan, calculates price, shows checkout form

2. **User submits form**
   - JavaScript: `POST /checkout/create-payment-intent`
   - Server: Creates Stripe Payment Intent, returns client secret
   - JavaScript: Uses Stripe.js to collect card and confirm payment
   - Stripe: Processes payment, returns payment intent ID

3. **Payment confirmed**
   - JavaScript: `POST /checkout/process` with payment intent ID
   - Server: Verifies payment with Stripe API
   - Server: Updates organization subscription
   - Server: Creates invoice record
   - Server: Logs activity
   - Server: Returns invoice ID

4. **Redirect to success**
   - Browser: `GET /checkout/success?invoice_id=123`
   - Server: Loads invoice, shows success page

---

## 📋 Database Changes

When a payment is successful, these changes occur:

1. **Organization Updated**
   - `pricing_plan_id` → New plan ID
   - `billing_period` → 'monthly' or 'yearly'
   - `subscription_status` → 'active'
   - `subscription_start_date` → Current date
   - `next_billing_date` → +1 month or +1 year
   - `company`, `billing_email`, `vat_number`, `address_line1`, `address_line2`, `city`, `postcode`, `country` → From checkout form

2. **Invoice Created**
   - New record in `invoices` table
   - Status: 'paid'
   - Provider: 'stripe'
   - Includes all organization billing info snapshot

3. **Invoice Line Item Created**
   - New record in `invoice_line_items` table
   - Description: Plan name + billing period
   - Quantity: 1
   - Unit price and subtotal

4. **Activity Logged**
   - New record in `activity_log` table
   - Subject: Organization
   - Description: "Subscribed to [Plan Name] ([Period])"

---

## 🎨 UI/UX Features

1. **Responsive Design**
   - Two-column layout on desktop (form + summary)
   - Single column on mobile
   - Dark theme matching your billing page

2. **Real-time Validation**
   - Card validation by Stripe Elements
   - Form field validation (HTML5)
   - Error messages displayed inline

3. **Loading States**
   - Submit button disabled during processing
   - Spinner animation
   - Button text changes to "Processing..."

4. **Error Handling**
   - Card errors shown below card input
   - General payment errors shown in red alert box
   - User-friendly error messages

5. **Security**
   - Stripe Elements = PCI DSS compliant
   - No card data touches your server
   - CSRF token protection
   - Auth middleware on all routes

---

## 🐛 Troubleshooting

### "Stripe API key not found"
- Check your `.env` file has `STRIPE_KEY` and `STRIPE_SECRET`
- Run `php artisan config:cache` to clear config cache

### "Payment Intent creation failed"
- Verify your `STRIPE_SECRET` key is correct
- Check it starts with `sk_test_` for test mode
- Look at Laravel logs: `storage/logs/laravel.log`

### "Card declined"
- Use test card `4242 4242 4242 4242`
- Make sure you're in Stripe test mode
- Check Stripe Dashboard → Logs for details

### Checkout page shows 404
- Run `php artisan route:clear`
- Verify routes exist: `php artisan route:list | grep checkout`

### JavaScript errors in console
- Check browser console for errors
- Verify Stripe.js is loading: view page source, find `https://js.stripe.com/v3/`
- Check `STRIPE_KEY` is correctly passed to JavaScript

---

## 📊 Monitoring in Stripe Dashboard

1. **View Test Payments**
   - Go to: https://dashboard.stripe.com/test/payments
   - You'll see all test payments made

2. **View Payment Intents**
   - Go to: https://dashboard.stripe.com/test/payment_intents
   - Click on a payment to see details

3. **View Logs**
   - Go to: https://dashboard.stripe.com/test/logs
   - See all API requests and responses

4. **Test Webhooks**
   - Go to: https://dashboard.stripe.com/test/webhooks
   - Send test events to your endpoint

---

## 🚨 Important Notes

1. **Tax Calculation**
   - Currently hardcoded at 25% VAT
   - Location: `CheckoutController.php` line 162-164
   - Should be updated to use country-based tax rates

2. **Invoice Download**
   - Route `invoice.download` must exist
   - Or remove the download button from success page

3. **Production Deployment**
   - Switch to production keys: `pk_live_...` and `sk_live_...`
   - Test thoroughly in production mode
   - Enable webhooks for async payment notifications
   - Consider adding fraud detection rules in Stripe Dashboard

4. **Currency**
   - Currently shows DKK (Danish Kroner)
   - Can be changed in checkout views and controller

---

## 📝 Next Steps (Optional Improvements)

1. **Email Notifications**
   - Send confirmation email after successful payment
   - Send receipt with invoice attached

2. **Webhook Handler**
   - Create `WebhookController` for Stripe webhooks
   - Handle `payment_intent.succeeded` event
   - Handle `payment_intent.payment_failed` event

3. **Subscription Management**
   - Add ability to cancel subscription
   - Add ability to change billing period
   - Implement prorated upgrades/downgrades

4. **Invoice PDF Generation**
   - Use package like `barryvdh/laravel-dompdf`
   - Generate PDF invoices
   - Attach to confirmation emails

5. **Country-based Tax**
   - Integrate with Stripe Tax
   - Or implement tax rate lookup by country

6. **Save Payment Method**
   - Save card for future payments
   - Implement automatic renewals
   - Add payment method management page

---

## ✨ Summary

Your Stripe checkout integration is complete and ready to test! The system provides:

- ✅ Professional checkout page with billing form
- ✅ Secure card payment with Stripe Elements
- ✅ Automatic subscription activation
- ✅ Invoice generation
- ✅ Success and cancellation pages
- ✅ Multi-language support (EN/IT/DA)
- ✅ Responsive dark theme design

Just add your Stripe API keys to `.env` and you're ready to test!

For any issues, check the Laravel logs (`storage/logs/laravel.log`) and the Stripe Dashboard logs.
