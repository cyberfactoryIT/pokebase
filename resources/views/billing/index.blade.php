@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">
                <i class="fas fa-credit-card text-blue-400 mr-2"></i>
                {{ __('subscriptions.billing_title', [], 'Billing & Subscription') }}
            </h1>
            <p class="text-gray-400">{{ __('subscriptions.billing_subtitle', [], 'Manage your subscription, view invoices, and purchase history') }}</p>
        </div>

        @if(session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-400/30 text-green-300 px-4 py-3 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @php
            $membershipStatus = Auth::user()->membershipStatus();
            $plans = \App\Models\PricingPlan::all();
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Membership Card -->
            <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-white mb-1">
                            <i class="fas fa-star text-yellow-400 mr-2"></i>
                            {{ __('subscriptions.membership.title') }}
                        </h2>
                        <p class="text-sm text-gray-400">{{ __('subscriptions.membership.explanation') }}</p>
                    </div>
                </div>

                @if($membershipStatus['tier'] === 'free')
                    <!-- No Active Subscription -->
                    <div class="bg-white/5 border border-white/10 rounded-lg p-6 mb-4">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-500/20 rounded-full mb-4">
                                <i class="fas fa-star text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-white mb-2">{{ __('subscriptions.membership.no_active_membership') }}</h3>
                            <p class="text-gray-400 text-sm mb-4">Upgrade to unlock premium features</p>
                            <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold">
                                <i class="fas fa-crown"></i>
                                <span>View Plans</span>
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Active Subscription Info -->
                    <div class="space-y-4">
                        <div class="bg-gradient-to-br from-blue-900/30 to-blue-800/20 border border-blue-500/30 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-gray-400 text-sm">{{ __('subscriptions.membership.current_plan') }}</span>
                                <span class="px-3 py-1 bg-blue-600/20 border border-blue-500/30 rounded-full text-blue-300 font-semibold text-sm">
                                    {{ ucfirst($membershipStatus['tier']) }}
                                </span>
                            </div>
                            <div class="text-2xl font-bold text-white mb-1">{{ $membershipStatus['plan_name'] }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/5 border border-white/10 rounded-lg p-3">
                                <div class="text-gray-400 text-xs mb-1">{{ __('subscriptions.membership.status') }}</div>
                                <div class="flex items-center gap-2">
                                    @if($membershipStatus['is_cancelled'])
                                        <span class="inline-flex items-center gap-1 text-red-400 font-medium">
                                            <i class="fas fa-times-circle text-sm"></i>
                                            {{ __('subscriptions.membership.status_cancelled') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-green-400 font-medium">
                                            <i class="fas fa-check-circle text-sm"></i>
                                            {{ __('subscriptions.membership.status_active') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="bg-white/5 border border-white/10 rounded-lg p-3">
                                <div class="text-gray-400 text-xs mb-1">{{ __('subscriptions.membership.billing_period') }}</div>
                                <div class="text-white font-medium">{{ ucfirst($membershipStatus['billing_period'] ?? 'N/A') }}</div>
                            </div>
                        </div>

                        @if($membershipStatus['next_renewal'])
                        <div class="bg-white/5 border border-white/10 rounded-lg p-3">
                            <div class="text-gray-400 text-xs mb-1">{{ __('subscriptions.membership.next_renewal') }}</div>
                            <div class="text-white font-medium">{{ $membershipStatus['next_renewal']->format('M d, Y') }}</div>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex gap-3 pt-3 border-t border-white/10">
                            @if($membershipStatus['is_cancelled'])
                                <form method="POST" action="{{ route('billing.reactivateSubscription') }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">
                                        <i class="fas fa-redo mr-2"></i>
                                        {{ __('subscriptions.membership.reactivate_subscription') }}
                                    </button>
                                </form>
                            @else
                                <button onclick="document.getElementById('changePlanModal').classList.remove('hidden')" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                                    <i class="fas fa-exchange-alt mr-2"></i>
                                    {{ __('subscriptions.membership.change_plan') }}
                                </button>
                                <form method="POST" action="{{ route('billing.cancelSubscription') }}" onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 border border-red-500/30 text-red-300 rounded-lg transition font-medium">
                                        <i class="fas fa-ban mr-2"></i>
                                        {{ __('subscriptions.membership.cancel_subscription') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Deck Evaluation Card -->
            <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-white mb-1">
                            <i class="fas fa-layer-group text-purple-400 mr-2"></i>
                            {{ __('subscriptions.deck_evaluation.title') }}
                        </h2>
                        <p class="text-sm text-gray-400">{{ __('subscriptions.deck_evaluation.explanation') }}</p>
                    </div>
                </div>

                @php
                    $activeDeckPurchase = Auth::user()->activeDeckEvaluationPurchase()->first();
                    $recentPurchases = Auth::user()->deckEvaluationPurchases()
                        ->orderBy('purchased_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp

                @if($activeDeckPurchase)
                    <!-- Active Package -->
                    <div class="bg-gradient-to-br from-purple-900/30 to-purple-800/20 border border-purple-500/30 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-400 text-sm">Active Package</span>
                            <span class="px-3 py-1 bg-green-600/20 border border-green-500/30 rounded-full text-green-300 font-semibold text-sm">
                                Active
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <div class="text-gray-400 text-xs mb-1">Cards Used</div>
                                <div class="text-white font-bold text-lg">{{ $activeDeckPurchase->cards_used }} / {{ $activeDeckPurchase->cards_limit }}</div>
                            </div>
                            <div>
                                <div class="text-gray-400 text-xs mb-1">Expires</div>
                                <div class="text-white font-medium text-sm">{{ $activeDeckPurchase->expires_at->format('M d, Y') }}</div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        @php
                            $percentage = ($activeDeckPurchase->cards_used / $activeDeckPurchase->cards_limit) * 100;
                        @endphp
                        <div class="w-full bg-white/10 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="bg-white/5 border border-white/10 rounded-lg p-6 mb-4 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-500/20 rounded-full mb-4">
                            <i class="fas fa-layer-group text-purple-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">No Active Package</h3>
                        <p class="text-gray-400 text-sm mb-4">Purchase a deck evaluation package to analyze your collection</p>
                        <a href="{{ route('deck-evaluation.packages.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-semibold">
                            <i class="fas fa-shopping-cart"></i>
                            <span>View Packages</span>
                        </a>
                    </div>
                @endif

                <!-- Recent Purchases -->
                @if($recentPurchases->count() > 0)
                <div class="pt-4 border-t border-white/10">
                    <h3 class="text-sm font-semibold text-gray-400 mb-3">Recent Purchases</h3>
                    <div class="space-y-2">
                        @foreach($recentPurchases as $purchase)
                        <div class="bg-white/5 border border-white/10 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="text-white text-sm font-medium mb-1">{{ $purchase->cards_limit }} Cards Package</div>
                                    <div class="text-gray-400 text-xs">{{ $purchase->purchased_at->format('M d, Y') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm">
                                        @if($purchase->status === 'active')
                                            <span class="px-2 py-1 bg-green-600/20 text-green-300 rounded text-xs">Active</span>
                                        @elseif($purchase->status === 'expired')
                                            <span class="px-2 py-1 bg-gray-600/20 text-gray-400 rounded text-xs">Expired</span>
                                        @else
                                            <span class="px-2 py-1 bg-yellow-600/20 text-yellow-300 rounded text-xs">{{ ucfirst($purchase->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Invoices Section -->
        @if(Auth::user()->organization)
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-file-invoice text-green-400 mr-2"></i>
                    {{ __('subscriptions.invoices.title', [], 'Invoices') }}
                </h2>
            </div>

            @php
                $invoices = Auth::user()->organization->invoices()->latest()->paginate(10);
            @endphp

            @if($invoices->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-white/10">
                        <tr>
                            <th class="text-left text-gray-400 text-sm font-medium py-3 px-4">Invoice #</th>
                            <th class="text-left text-gray-400 text-sm font-medium py-3 px-4">Date</th>
                            <th class="text-left text-gray-400 text-sm font-medium py-3 px-4">Amount</th>
                            <th class="text-left text-gray-400 text-sm font-medium py-3 px-4">Status</th>
                            <th class="text-right text-gray-400 text-sm font-medium py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach($invoices as $invoice)
                        <tr class="hover:bg-white/5 transition">
                            <td class="py-3 px-4">
                                <span class="text-white font-medium">{{ $invoice->number }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-gray-300">{{ $invoice->issued_at->format('M d, Y') }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-white font-semibold">€{{ number_format($invoice->total_cents / 100, 2) }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($invoice->status === 'paid')
                                    <span class="px-2 py-1 bg-green-600/20 border border-green-500/30 text-green-300 rounded text-xs font-medium">Paid</span>
                                @elseif($invoice->status === 'open')
                                    <span class="px-2 py-1 bg-yellow-600/20 border border-yellow-500/30 text-yellow-300 rounded text-xs font-medium">Open</span>
                                @else
                                    <span class="px-2 py-1 bg-red-600/20 border border-red-500/30 text-red-300 rounded text-xs font-medium">{{ ucfirst($invoice->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('billing.invoice.show', $invoice) }}" target="_blank" class="px-3 py-1.5 bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/30 text-blue-300 rounded-lg text-xs font-medium transition">
                                        <i class="fas fa-eye mr-1"></i>
                                        View
                                    </a>
                                    <a href="{{ route('billing.invoice.receipt', $invoice) }}" target="_blank" class="px-3 py-1.5 bg-gray-600/20 hover:bg-gray-600/30 border border-gray-500/30 text-gray-300 rounded-lg text-xs font-medium transition">
                                        <i class="fas fa-download mr-1"></i>
                                        Download
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $invoices->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-500/20 rounded-full mb-4">
                    <i class="fas fa-file-invoice text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-400">No invoices yet</p>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Change Plan Modal -->
<div id="changePlanModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/75 transition-opacity" onclick="document.getElementById('changePlanModal').classList.add('hidden')"></div>

        <div class="inline-block align-bottom bg-[#161615] rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/15">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">
                        <i class="fas fa-exchange-alt text-blue-400 mr-2"></i>
                        {{ __('subscriptions.membership.change_plan') }}
                    </h3>
                    <button onclick="document.getElementById('changePlanModal').classList.add('hidden')" class="text-gray-400 hover:text-white transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('billing.confirmChangePlan') }}">
                    @csrf
                    
                    <!-- Billing Period -->
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm mb-2">Billing Period</label>
                        <select name="billing_period" id="billing_period" class="w-full bg-[#0d0d0c] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500/50 transition">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly (Save 20%)</option>
                        </select>
                    </div>

                    <!-- Plan Selection -->
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm mb-2">Select Plan</label>
                        <select name="plan_id" id="plan_id_select" class="w-full bg-[#0d0d0c] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500/50 transition" required>
                            <option value="">Choose a plan...</option>
                            @foreach($plans as $plan)
                                @if($plan->name !== 'Free')
                                    <option value="{{ $plan->id }}" 
                                            data-monthly="{{ number_format($plan->monthly_price_cents / 100, 2) }}" 
                                            data-yearly="{{ number_format($plan->yearly_price_cents / 100, 2) }}"
                                            {{ strtolower($plan->name) === $membershipStatus['tier'] ? 'disabled' : '' }}>
                                        {{ $plan->name }} - €{{ number_format($plan->monthly_price_cents / 100, 2) }}/month
                                        {{ strtolower($plan->name) === $membershipStatus['tier'] ? '(Current)' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Coupon Code (Optional) -->
                    <div class="mb-6">
                        <label class="block text-gray-400 text-sm mb-2">Coupon Code (Optional)</label>
                        <input type="text" name="coupon_code" placeholder="Enter coupon code" 
                               class="w-full bg-[#0d0d0c] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500/50 transition">
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('changePlanModal').classList.add('hidden')" 
                                class="flex-1 px-6 py-3 bg-gray-600/20 hover:bg-gray-600/30 border border-gray-500/30 text-gray-300 rounded-lg transition font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold">
                            Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Update plan prices when billing period changes
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('billing_period');
    const planSelect = document.getElementById('plan_id_select');
    
    function updatePlanLabels() {
        const period = periodSelect.value;
        Array.from(planSelect.options).forEach(opt => {
            if (!opt.value) return;
            const price = period === 'yearly' ? opt.getAttribute('data-yearly') : opt.getAttribute('data-monthly');
            const planName = opt.textContent.split(' - ')[0];
            const current = opt.textContent.includes('(Current)') ? ' (Current)' : '';
            opt.textContent = `${planName} - €${price}/${period === 'yearly' ? 'year' : 'month'}${current}`;
        });
    }
    
    periodSelect.addEventListener('change', updatePlanLabels);
});
</script>
@endsection
