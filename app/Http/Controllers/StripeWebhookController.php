<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Invoice;
use App\Models\PricingPlan;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhooks
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (!$webhookSecret) {
            \Log::error('Stripe webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            \Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            \Log::error('Stripe webhook error', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Webhook error'], 400);
        }

        // Handle different event types
        try {
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
                
                default:
                    \Log::info('Unhandled Stripe webhook event', [
                        'type' => $event->type
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error processing Stripe webhook', [
                'event_type' => $event->type,
                'error' => $e->getMessage()
            ]);
            // Still return 200 to acknowledge receipt
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle successful invoice payment (subscription renewal)
     */
    private function handleInvoicePaymentSucceeded($stripeInvoice)
    {
        $subscriptionId = $stripeInvoice->subscription;
        
        if (!$subscriptionId) {
            \Log::info('Invoice payment succeeded but not from subscription', [
                'invoice_id' => $stripeInvoice->id
            ]);
            return;
        }

        $org = Organization::where('stripe_subscription_id', $subscriptionId)->first();
        
        if (!$org) {
            \Log::warning('Organization not found for subscription', [
                'subscription_id' => $subscriptionId
            ]);
            return;
        }

        // Update renew date based on subscription period end
        $org->renew_date = \Carbon\Carbon::createFromTimestamp($stripeInvoice->period_end);
        $org->subscription_cancelled = 0;
        $org->save();

        // Create invoice record for this renewal
        $this->createInvoiceFromStripe($org, $stripeInvoice);

        \Log::info('Subscription renewed successfully', [
            'organization_id' => $org->id,
            'subscription_id' => $subscriptionId,
            'next_renewal' => $org->renew_date
        ]);
    }

    /**
     * Handle failed payment
     */
    private function handleInvoicePaymentFailed($stripeInvoice)
    {
        $subscriptionId = $stripeInvoice->subscription;
        
        if (!$subscriptionId) {
            return;
        }

        $org = Organization::where('stripe_subscription_id', $subscriptionId)->first();
        
        if (!$org) {
            return;
        }

        \Log::warning('Subscription payment failed', [
            'organization_id' => $org->id,
            'subscription_id' => $subscriptionId,
            'invoice_id' => $stripeInvoice->id
        ]);

        // Notify organization admins
        $org->users()->whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->each(function($user) use ($stripeInvoice) {
            $user->notify(new PaymentFailedNotification($stripeInvoice));
        });
    }

    /**
     * Handle subscription cancellation
     */
    private function handleSubscriptionDeleted($subscription)
    {
        $org = Organization::where('stripe_subscription_id', $subscription->id)->first();
        
        if (!$org) {
            return;
        }

        $org->subscription_cancelled = 1;
        $org->cancellation_subscription_date = now();
        $org->pricing_plan_id = null;
        $org->save();

        \Log::info('Subscription cancelled', [
            'organization_id' => $org->id,
            'subscription_id' => $subscription->id
        ]);
    }

    /**
     * Handle subscription updates (plan changes, etc.)
     */
    private function handleSubscriptionUpdated($subscription)
    {
        $org = Organization::where('stripe_subscription_id', $subscription->id)->first();
        
        if (!$org) {
            return;
        }

        // Update next billing date
        $org->renew_date = \Carbon\Carbon::createFromTimestamp($subscription->current_period_end);
        
        // Check if subscription is set to cancel
        if ($subscription->cancel_at_period_end) {
            $org->subscription_cancelled = 1;
            $org->cancellation_subscription_date = now();
        }
        
        $org->save();

        \Log::info('Subscription updated', [
            'organization_id' => $org->id,
            'subscription_id' => $subscription->id,
            'cancel_at_period_end' => $subscription->cancel_at_period_end
        ]);
    }

    /**
     * Create invoice record from Stripe invoice
     */
    private function createInvoiceFromStripe(Organization $org, $stripeInvoice)
    {
        // Calculate tax (25% VAT)
        $totalCents = $stripeInvoice->amount_paid;
        $taxRate = 0.25;
        $subtotalCents = (int) ($totalCents / (1 + $taxRate));
        $taxCents = $totalCents - $subtotalCents;

        $invoice = Invoice::create([
            'organization_id' => $org->id,
            'number' => 'INV-' . $stripeInvoice->number ?? now()->format('YmdHis'),
            'provider' => 'stripe',
            'provider_id' => $stripeInvoice->id,
            'payment_type' => 'subscription',
            'currency' => strtoupper($stripeInvoice->currency),
            'subtotal_cents' => $subtotalCents,
            'tax_cents' => $taxCents,
            'total_cents' => $totalCents,
            'status' => 'paid',
            'issued_at' => now(),
            'paid_at' => \Carbon\Carbon::createFromTimestamp($stripeInvoice->status_transitions->paid_at),
            'org_name' => $org->name,
            'org_company' => $org->company,
            'org_billing_email' => $org->billing_email,
            'org_vat' => $org->vat_number,
            'org_address' => $org->address_line1,
            'org_city' => $org->city,
            'org_country' => $org->country,
        ]);

        // Create invoice item
        $plan = $org->pricingPlan;
        $billingPeriod = $org->billing_period ?? 'monthly';
        
        $invoice->items()->create([
            'description' => ($plan ? $plan->name : 'Subscription') . ' - ' . ucfirst($billingPeriod) . ' (Renewal)',
            'quantity' => 1,
            'unit_price_cents' => $subtotalCents,
            'total_cents' => $subtotalCents,
        ]);

        return $invoice;
    }
}
