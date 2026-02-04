<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class CheckoutController extends Controller
{
    public function __construct()
    {
        // Middleware handled in routes
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Show checkout page
     */
    public function show(Request $request)
    {
        $planId = $request->input('plan_id');
        $billingPeriod = $request->input('billing_period', 'monthly');

        $plan = PricingPlan::findOrFail($planId);
        $user = Auth::user();
        
        // Get or auto-create organization
        if (!$user->organization) {
            $org = \App\Models\Organization::create([
                'name' => $user->name . "'s Organization",
                'code' => 'ORG-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'slug' => \Illuminate\Support\Str::slug($user->name) . '-' . time(),
            ]);
            $user->organization_id = $org->id;
            $user->save();
        }

        $org = $user->organization;

        // Calculate price
        $priceInCents = $billingPeriod === 'yearly' 
            ? $plan->yearly_price_cents 
            : $plan->monthly_price_cents;

        return view('checkout.index', [
            'plan' => $plan,
            'billingPeriod' => $billingPeriod,
            'priceInCents' => $priceInCents,
            'priceFormatted' => number_format($priceInCents / 100, 2),
            'organization' => $org,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    /**
     * Create Stripe Subscription (replaces Payment Intent for recurring billing)
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $planId = $request->input('plan_id');
            $billingPeriod = $request->input('billing_period', 'monthly');

            $plan = PricingPlan::findOrFail($planId);
            $user = Auth::user();
            $org = $user->organization;

            // Get Stripe Price ID based on billing period
            $stripePriceId = $billingPeriod === 'yearly' 
                ? $plan->stripe_yearly_price_id 
                : $plan->stripe_monthly_price_id;

            if (!$stripePriceId) {
                throw new \Exception('Stripe price ID not configured for this plan. Please contact support.');
            }

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // Create or retrieve Stripe Customer
            if ($org->stripe_customer_id) {
                $customer = $stripe->customers->retrieve($org->stripe_customer_id);
            } else {
                $customer = $stripe->customers->create([
                    'email' => $org->billing_email ?? $user->email,
                    'name' => $org->company ?? $org->name,
                    'metadata' => [
                        'organization_id' => $org->id,
                        'user_id' => $user->id,
                    ],
                ]);

                // Save customer ID
                $org->stripe_customer_id = $customer->id;
                $org->save();
            }

            // Create a Setup Intent to collect payment method first
            $setupIntent = $stripe->setupIntents->create([
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'usage' => 'off_session',
                'metadata' => [
                    'organization_id' => $org->id,
                    'plan_id' => $plan->id,
                    'billing_period' => $billingPeriod,
                    'price_id' => $stripePriceId,
                ],
            ]);

            \Log::info('Setup Intent created for subscription', [
                'setup_intent_id' => $setupIntent->id,
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
            ]);

            return response()->json([
                'clientSecret' => $setupIntent->client_secret,
                'setupIntentId' => $setupIntent->id,
                'type' => 'setup', // Tell frontend this is a setup intent, not payment intent
            ]);

        } catch (ApiErrorException $e) {
            \Log::error('Stripe Setup Intent creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'plan_id' => $planId ?? null,
            ]);
            
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Setup Intent creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            
            return response()->json([
                'error' => 'Unable to create subscription. Please try again.',
            ], 500);
        }
    }

    /**
     * Process payment after Stripe confirmation
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'setup_intent_id' => 'required|string', // Changed from payment_intent_id
            'plan_id' => 'required|exists:pricing_plans,id',
            'billing_period' => 'required|in:monthly,yearly',
            'company' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postcode' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
        ]);

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            
            // Retrieve setup intent to get payment method
            $setupIntent = $stripe->setupIntents->retrieve($request->setup_intent_id);

            \Log::info('Processing subscription with setup intent', [
                'setup_intent_id' => $request->setup_intent_id,
                'setup_intent_status' => $setupIntent->status,
                'payment_method' => $setupIntent->payment_method,
            ]);

            if ($setupIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'error' => 'Payment method setup was not successful. Status: ' . $setupIntent->status
                ], 400);
            }

            $user = Auth::user();
            $org = $user->organization;
            $plan = PricingPlan::findOrFail($request->plan_id);

            // Get price ID from setup intent metadata
            $stripePriceId = $setupIntent->metadata->price_id;
            
            // Now create the subscription with the confirmed payment method
            $subscription = $stripe->subscriptions->create([
                'customer' => $setupIntent->customer,
                'items' => [
                    ['price' => $stripePriceId],
                ],
                'default_payment_method' => $setupIntent->payment_method,
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'organization_id' => $org->id,
                    'plan_id' => $plan->id,
                    'billing_period' => $request->billing_period,
                ],
            ]);
            
            // Retrieve subscription to get full details including current_period_end
            $subscription = $stripe->subscriptions->retrieve($subscription->id);
            
            \Log::info('Subscription created with payment method', [
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'payment_method' => $setupIntent->payment_method,
                'current_period_end' => $subscription->current_period_end,
            ]);

            // Update organization billing info
            $org->update([
                'company' => $request->company,
                'billing_email' => $request->billing_email,
                'vat_number' => $request->vat_number,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'postcode' => $request->postcode,
                'country' => $request->country,
            ]);

            // Update organization plan and subscription info
            $org->pricing_plan_id = $plan->id;
            $org->billing_period = $request->billing_period;
            $org->payment_type = 'subscription'; // Mark as recurring subscription
            $org->stripe_subscription_id = $subscription->id; // Save subscription ID
            $org->subscription_date = now();
            
            // Set renew_date based on billing period if current_period_end is not available
            if ($subscription->current_period_end) {
                $org->renew_date = \Carbon\Carbon::createFromTimestamp($subscription->current_period_end);
            } else {
                // Calculate renew date based on billing period
                $org->renew_date = $request->billing_period === 'yearly' 
                    ? now()->addYear() 
                    : now()->addMonth();
            }
            
            $org->subscription_cancelled = 0;
            $org->save();
            
            // End trial if user was on one (they're now paying)
            if ($org->isOnTrial()) {
                \Log::info('Ending trial - user upgraded to paid subscription', [
                    'organization_id' => $org->id,
                    'trial_plan_id' => $org->trial_plan_id,
                    'trial_expires_at' => $org->trial_expires_at,
                ]);
                $org->endTrial();
            }

            // Create invoice
            $priceInCents = $request->billing_period === 'yearly' 
                ? $plan->yearly_price_cents 
                : $plan->monthly_price_cents;

            $taxRate = 0.25; // 25% VAT (adjust based on country)
            $subtotalCents = (int) ($priceInCents / (1 + $taxRate));
            $taxCents = $priceInCents - $subtotalCents;

            $invoice = Invoice::create([
                'organization_id' => $org->id,
                'number' => Invoice::generateInvoiceNumber(),
                'provider' => 'stripe',
                'provider_id' => $subscription->latest_invoice,
                'payment_type' => 'subscription',
                'currency' => 'dkk',
                'subtotal_cents' => $subtotalCents,
                'tax_cents' => $taxCents,
                'total_cents' => $priceInCents,
                'status' => 'paid',
                'issued_at' => now(),
                'paid_at' => now(),
                'org_name' => $org->name,
                'org_company' => $org->company,
                'org_billing_email' => $org->billing_email,
                'org_vat' => $org->vat_number,
                'org_address' => $org->address_line1,
                'org_city' => $org->city,
                'org_country' => $org->country,
            ]);

            // Create invoice item
            $billingPeriod = $request->billing_period === 'yearly' 
                ? __('billing.invoice.period_yearly') 
                : __('billing.invoice.period_monthly');
            
            $invoice->items()->create([
                'description' => __('billing.invoice.plan_description', [
                    'plan' => $plan->name,
                    'period' => $billingPeriod
                ]),
                'quantity' => 1,
                'unit_price_cents' => $subtotalCents,
                'total_cents' => $subtotalCents,
            ]);

            // Log activity
            \App\Models\ActivityLog::logActivity(
                'checkout',
                'subscription_purchased',
                [
                    'plan' => $plan->name,
                    'billing_period' => $request->billing_period,
                    'amount' => $priceInCents / 100,
                ],
                $org->id,
                Auth::id()
            );

            // Send confirmation email with invoice details
            Auth::user()->notify(new \App\Notifications\SubscriptionConfirmationNotification(
                $org,
                $invoice,
                $org->renew_date,
                $request->billing_period
            ));

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'message' => 'Subscription activated successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Payment processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show success page
     */
    public function success(Request $request)
    {
        $invoiceId = $request->input('invoice_id');
        
        if (!$invoiceId) {
            return Redirect::route('billing.index')
                ->with('error', 'Invoice not found.');
        }
        
        $invoice = Invoice::with(['items', 'organization'])->find($invoiceId);

        return view('checkout.success', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show cancel page
     */
    public function cancel()
    {
        return view('checkout.cancel');
    }
}
