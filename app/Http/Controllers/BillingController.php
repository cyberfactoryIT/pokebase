<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcesAdmin;
use App\Models\Invoice;
use App\Models\PricingPlan;
use App\Models\Organization;
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
        $org = Auth::user()->organization;
        $org->subscription_cancelled = 0;
        $org->cancellation_subscription_date = null;
        $org->reactivate_subscription_date = now();
        $org->save();
        \App\Models\ActivityLog::logActivity(
            'billing',
            'reactivate_subscription',
            [
                'date' => now()->toDateTimeString(),
            ],
            $org->id,
            Auth::id()
        );
        return Redirect::route('billing.index')->with('status', __('messages.subscription_reactivated'));
    }

    public function cancelSubscription(Request $request)
    {
        $org = Auth::user()->organization;
        $org->subscription_cancelled = 1;
        $org->cancellation_subscription_date = now();
        $org->save();
         \App\Models\ActivityLog::logActivity(
            'billing',
            'cancel_subscription',
            [
                'date' => now()->toDateTimeString(),
            ],
            $org->id,
            Auth::id()
        );
        return Redirect::route('billing.index')->with('status', __('messages.subscription_cancelled'));
    }

    public function index()
    {
        $org = Auth::user()->organization;
        $plans = PricingPlan::with('features')->get();
        $invoices = $org->invoices()->latest()->paginate(15);
        return view('billing.index', compact('org', 'plans', 'invoices'));
    }

    public function changePlan(Request $request)
    {
        $org = Auth::user()->organization;
        $request->validate([
            'plan_id' => 'required|exists:pricing_plans,id',
            'billing_period' => 'required|in:monthly,yearly',
            'coupon_code' => 'nullable|string|max:32',
        ]);
        $plan = PricingPlan::findOrFail($request->plan_id);
        $period = $request->billing_period;
        $base = $period === 'yearly' ? $plan->yearly_price_cents : $plan->monthly_price_cents;
        $pricing = app(PlanPricing::class)->currentPriceForPlan($plan, $org, $request->coupon_code, null, $base);
        $org->pricing_plan_id = $plan->id;
        $org->subscription_date = now();
        $org->renew_date = $period === 'yearly' ? now()->addYear() : now()->addMonth();
        if (!empty($request->coupon_code)) {
            $promo = app(\App\Services\PromotionEngine::class)
                ->resolveApplicable($plan, $org, $request->coupon_code);
            if ($promo) {
                $org->applyPromotion($promo, $promo->ends_at, $request->coupon_code);
            } else {
                $org->promotion_code = null;
                $org->end_promotion_date = null;
            }
        }
        $oldPlanId = $org->getOriginal('pricing_plan_id');
        $oldPlanName = $oldPlanId ? \App\Models\PricingPlan::find($oldPlanId)?->name : null;
        \App\Models\ActivityLog::logActivity(
            'billing',
            'change_plan',
            [
                'old_plan_id' => $oldPlanName,
                'new_plan_id' => $plan->name,
                'coupon_code' => $request->coupon_code,
                'discount_cents' => $pricing['discount_cents'],
                'period' => $period,
            ],
            $org->id,
            Auth::id()
        );
        $org->save();
        $lastInvoice = \App\Models\Invoice::whereYear('issued_at', now()->year)
            ->orderByDesc('number')
            ->first();
        if ($lastInvoice && preg_match('/INV-\d{4}-(\d{6})/', $lastInvoice->number, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        } else {
            $nextSeq = 1;
        }
        $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);
        $totalCents = $pricing['final_cents'];
        $taxCents = intval(round($totalCents * 0.25 / 1.25));
        $subtotalCents = $totalCents - $taxCents;
        $start = now();
        $end = $period === 'yearly' ? now()->addYear() : now()->addMonth();
        $description = $plan->name . ' (' . ($period === 'yearly' ? '1 anno' : '1 mese') . ') dal ' . $start->format('d/m/Y') . ' al ' . $end->format('d/m/Y');
        $invoice = $org->invoices()->create([
            'number' => $invoiceNumber,
            'currency' => $plan->currency,
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $pricing['discount_cents'],
            'tax_cents' => $taxCents,
            'total_cents' => $totalCents,
            'status' => 'paid', // Imposta di default su paid
            'issued_at' => now(),
            'coupon_code' => $request->coupon_code,
            'promotion_snapshot' => $pricing['applied'] ? $pricing['applied'][0]->toArray() : null,
            'billing_period' => $period,
            'description' => $description,
            'org_name' => $org->name,
            'org_company' => $org->company,
            'org_billing_email' => $org->billing_email,
            'org_vat' => $org->vat_number,
            'org_address' => $org->address_line1,
            'org_city' => $org->city,
            'org_country' => $org->country,
        ]);
        $invoice->items()->create([
            'description' => $description,
            'quantity' => 1,
            'unit_price_cents' => $subtotalCents,
            'total_cents' => $subtotalCents,
        ]);
        app(PromotionEngine::class)->recordRedemption($org, $pricing['applied'][0] ?? null, $request->coupon_code);
        // Mostra la thankyou page invece di redirect
        $amount = $pricing['final_cents'];
        return view('billing.thankyou', compact('plan', 'amount', 'period', 'start', 'end'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $user = Auth::user();
        $org = $user->organization;
        $isSuperadmin = $user->hasRole('superadmin');
        $isAdmin = $user->hasRole('admin');
        // Superadmin può vedere tutte le fatture
        if ($isSuperadmin) {
            return view('billing.invoice', compact('invoice', 'org'));
        }
        // Admin può vedere solo le fatture della propria organizzazione
        if ($isAdmin && $invoice->organization_id === $org->id) {
            return view('billing.invoice', compact('invoice', 'org'));
        }
        // Altri utenti: 403
        abort(403);
    }

    public function downloadReceipt(Invoice $invoice)
    {
        $user = Auth::user();
        $org = $user->organization;
        $isSuperadmin = $user->hasRole('superadmin');
        $isAdmin = $user->hasRole('admin');
        // Superadmin può scaricare tutte le fatture
        if ($isSuperadmin || ($isAdmin && $invoice->organization_id === $org->id)) {
            if (!$invoice->receipt_pdf_path || !file_exists(storage_path('app/' . $invoice->receipt_pdf_path))) {
                abort(404);
            }
            return response()->download(storage_path('app/' . $invoice->receipt_pdf_path));
        }
        // Altri utenti: 403
        abort(403);
    }

    public function showChangePlanConfirmation(Request $request)
    {
        $org = Auth::user()->organization;
        $plan = \App\Models\PricingPlan::findOrFail($request->plan_id);
        $period = $request->billing_period;
        $coupon_code = $request->coupon_code;
        $base = $period === 'yearly' ? $plan->yearly_price_cents : $plan->monthly_price_cents;
        $promotionError = null;
        $promo = null;
        if (!empty($coupon_code)) {
            $promo = app(\App\Services\PromotionEngine::class)
                ->resolveApplicable($plan, $org, $coupon_code);
            if ($promo) {
                // Check if already redeemed by this org
                $alreadyRedeemed = $org->promotions()->where('promotion_id', $promo->id)->exists();
                if ($alreadyRedeemed) {
                    $promotionError = __('messages.promotion_already_redeemed');
                    // Exclude promotion from calculation
                    $coupon_code = null;
                }
            } else {
                $promotionError = __('messages.invalid_or_expired_promotion_code');
                $coupon_code = null;
            }
        }
        $pricing = app(\App\Services\PlanPricing::class)->currentPriceForPlan($plan, $org, $coupon_code, null, $base);
        $amount = $pricing['final_cents'];
        $start = now();
        $end = $period === 'yearly' ? now()->addYear() : now()->addMonth();
        return view('billing.confirm-change-plan', compact('plan', 'amount', 'period', 'start', 'end', 'coupon_code', 'promotionError'));
    }
}
