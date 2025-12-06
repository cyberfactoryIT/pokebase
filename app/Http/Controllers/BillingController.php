<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcesAdmin;
use App\Models\Invoice;
use App\Models\PricingPlan;
use App\Services\PlanPricing;
use App\Services\PromotionEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class BillingController extends Controller
{
    use EnforcesAdmin;

    public function __construct()
    {
        $this->enforceAdmin();
    }

    public function reactivateSubscription(Request $request)
    {
        if (!config('organizations.enabled')) {
            abort(404);
        }

        $org = Auth::user()->organization;

        if (!$org) {
            abort(403);
        }

        $org->subscription_cancelled = 0;
        $org->cancellation_subscription_date = null;
        $org->reactivate_subscription_date = now();
        $org->save();

        \App\Models\ActivityLog::logActivity(
            'billing',
            'reactivate_subscription',
            array(
                'date' => now()->toDateTimeString(),
            ),
            $org->id,
            Auth::id()
        );

        return Redirect::route('billing.index')
            ->with('status', __('messages.subscription_reactivated'));
    }

    public function cancelSubscription(Request $request)
    {
        if (!config('organizations.enabled')) {
            abort(404);
        }

        $org = Auth::user()->organization;

        if (!$org) {
            abort(403);
        }

        $org->subscription_cancelled = 1;
        $org->cancellation_subscription_date = now();
        $org->save();

        \App\Models\ActivityLog::logActivity(
            'billing',
            'cancel_subscription',
            array(
                'date' => now()->toDateTimeString(),
            ),
            $org->id,
            Auth::id()
        );

        return Redirect::route('billing.index')
            ->with('status', __('messages.subscription_cancelled'));
    }

    public function index()
    {
        if (!config('organizations.enabled')) {
            abort(404);
        }

        $org = Auth::user()->organization;

        if (!$org) {
            abort(403);
        }

        $plans    = PricingPlan::with('features')->get();
        $invoices = $org->invoices()->latest()->paginate(15);

        return view('billing.index', compact('org', 'plans', 'invoices'));
    }

    public function changePlan(Request $request)
    {
        if (!config('organizations.enabled')) {
            abort(404);
        }

        $org = Auth::user()->organization;

        if (!$org) {
            abort(403);
        }

        $request->validate(array(
            'plan_id'        => 'required|exists:pricing_plans,id',
            'billing_period' => 'required|in:monthly,yearly',
            'coupon_code'    => 'nullable|string|max:32',
        ));

        $plan   = PricingPlan::findOrFail($request->plan_id);
        $period = $request->billing_period;

        $base = ($period === 'yearly')
            ? $plan->yearly_price_cents
            : $plan->monthly_price_cents;

        $pricing = app(PlanPricing::class)->currentPriceForPlan(
            $plan,
            $org,
            $request->coupon_code,
            null,
            $base
        );

        // aggiorno il piano dell'organizzazione
        $org->pricing_plan_id   = $plan->id;
        $org->subscription_date = now();
        $org->renew_date        = ($period === 'yearly')
            ? now()->copy()->addYear()
            : now()->copy()->addMonth();

        if (!empty($request->coupon_code)) {
            $promo = app(PromotionEngine::class)->resolveApplicable(
                $plan,
                $org,
                $request->coupon_code
            );

            if ($promo) {
                $org->applyPromotion($promo, $promo->ends_at, $request->coupon_code);
            } else {
                $org->promotion_code     = null;
                $org->end_promotion_date = null;
            }
        }

        // log cambio piano
        $oldPlanId   = $org->getOriginal('pricing_plan_id');
        $oldPlanName = $oldPlanId
            ? PricingPlan::find($oldPlanId)->name
            : null;

        $discountCents = isset($pricing['discount_cents']) ? $pricing['discount_cents'] : 0;

        \App\Models\ActivityLog::logActivity(
            'billing',
            'change_plan',
            array(
                'old_plan_id'    => $oldPlanName,
                'new_plan_id'    => $plan->name,
                'coupon_code'    => $request->coupon_code,
                'discount_cents' => $discountCents,
                'period'         => $period,
            ),
            $org->id,
            Auth::id()
        );

        $org->save();

        // generazione numero fattura
        $lastInvoice = Invoice::whereYear('issued_at', now()->year)
            ->orderByDesc('number')
            ->first();

        if (
            $lastInvoice
            && preg_match('/INV-\d{4}-(\d{6})/', $lastInvoice->number, $matches)
        ) {
            $nextSeq = (int) $matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

        $totalCents    = $pricing['final_cents'];
        $taxCents      = (int) round($totalCents * 0.25 / 1.25);
        $subtotalCents = $totalCents - $taxCents;
        $start         = now();
        $end           = ($period === 'yearly')
            ? $start->copy()->addYear()
            : $start->copy()->addMonth();

        $description = $plan->name
            . ' (' . ($period === 'yearly' ? '1 anno' : '1 mese') . ') '
            . 'dal ' . $start->format('d/m/Y')
            . ' al ' . $end->format('d/m/Y');

        // promo applicata (se c'è)
        $appliedPromotion = (isset($pricing['applied'][0]) ? $pricing['applied'][0] : null);

        // creo la fattura
        $invoice = $org->invoices()->create(array(
            'number'             => $invoiceNumber,
            'currency'           => $plan->currency,
            'subtotal_cents'     => $subtotalCents,
            'discount_cents'     => $discountCents,
            'tax_cents'          => $taxCents,
            'total_cents'        => $totalCents,
            'status'             => 'paid',
            'issued_at'          => now(),
            'coupon_code'        => $request->coupon_code,
            'promotion_snapshot' => $appliedPromotion ? $appliedPromotion->toArray() : null,
            'billing_period'     => $period,
            'description'        => $description,
            'org_name'           => $org->name,
            'org_company'        => $org->company,
            'org_billing_email'  => $org->billing_email,
            'org_vat'            => $org->vat_number,
            'org_address'        => $org->address_line1,
            'org_city'           => $org->city,
            'org_country'        => $org->country,
        ));

        $invoice->items()->create(array(
            'description'      => $description,
            'quantity'         => 1,
            'unit_price_cents' => $subtotalCents,
            'total_cents'      => $subtotalCents,
        ));

        app(PromotionEngine::class)->recordRedemption(
            $org,
            $appliedPromotion,
            $request->coupon_code
        );

        $amount = $totalCents;

        return view('billing.thankyou', compact('plan', 'amount', 'period', 'start', 'end'));
    }

    public function showInvoice(Invoice $invoice)
    {
        if (!config('organizations.enabled')) {
            abort(404);
        }

        $user         = Auth::user();
        $org          = $user->organization;
        $isSuperadmin = $user->hasRole('superadmin');
        $isAdmin      = $user->hasRole('admin');

        if (!$org) {
            abort(403);
        }

        if ($isSuperadmin) {
            return view('billing.invoice', compact('invoice', 'org'));
        }

        if ($isAdmin && $invoice->organization_id === $org->id) {
            return view('billing.invoice', compact('invoice', 'org'));
        }

        abort(403);
    }

    public function downloadReceipt(Invoice $invoice)
    {
        if (!config('organizations.enabled')) {
            abort(404);
        }

        $user         = Auth::user();
        $org          = $user->organization;
        $isSuperadmin = $user->hasRole('superadmin');
        $isAdmin      = $user->hasRole('admin');

        if (!$org) {
            abort(403);
        }

        if ($isSuperadmin || ($isAdmin && $invoice->organization_id === $org->id)) {
            if (
                !$invoice->receipt_pdf_path
                || !file_exists(storage_path('app/' . $invoice->receipt_pdf_path))
            ) {
                abort(404);
            }

            return response()->download(
                storage_path('app/' . $invoice->receipt_pdf_path)
            );
        }

        abort(403);
    }

    public function showChangePlanConfirmation(Request $request)
    {
        if (!config('organizations.enabled')) {
            abort(404);
        }

        $org = Auth::user()->organization;

        if (!$org) {
            abort(403);
        }

        $plan        = PricingPlan::findOrFail($request->plan_id);
        $period      = $request->billing_period;
        $coupon_code = $request->coupon_code;

        $base = ($period === 'yearly')
            ? $plan->yearly_price_cents
            : $plan->monthly_price_cents;

        $promotionError = null;
        $promo          = null;

        if (!empty($coupon_code)) {
            $promo = app(PromotionEngine::class)->resolveApplicable($plan, $org, $coupon_code);

            if ($promo) {
                $alreadyRedeemed = $org->promotions()
                    ->where('promotion_id', $promo->id)
                    ->exists();

                if ($alreadyRedeemed) {
                    $promotionError = __('messages.promotion_already_redeemed');
                    $coupon_code    = null;
                }
            } else {
                $promotionError = __('messages.invalid_or_expired_promotion_code');
                $coupon_code    = null;
            }
        }

        $pricing = app(PlanPricing::class)->currentPriceForPlan(
            $plan,
            $org,
            $coupon_code,
            null,
            $base
        );

        $amount = $pricing['final_cents'];
        $start  = now();
        $end    = ($period === 'yearly')
            ? $start->copy()->addYear()
            : $start->copy()->addMonth();

        return view('billing.change-plan-confirmation', array(
            'org'            => $org,
            'plan'           => $plan,
            'period'         => $period,
            'coupon_code'    => $coupon_code,
            'promotionError' => $promotionError,
            'promo'          => $promo,
            'pricing'        => $pricing,
            'amount'         => $amount,
            'start'          => $start,
            'end'            => $end,
        ));
    }
}
