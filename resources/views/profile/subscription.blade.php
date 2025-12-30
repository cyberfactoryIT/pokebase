@extends('layouts.app')

@section('page_title', __('profile/edit.page_title'))

@section('content')
<div class="max-w-6xl mx-auto">
    
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-900/20 border border-green-500/50 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <i class="fa fa-check-circle text-green-400 text-xl"></i>
                <p class="text-green-400 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-900/20 border border-red-500/50 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <i class="fa fa-times-circle text-red-400 text-xl"></i>
                <p class="text-red-400 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-900/20 border border-red-500/50 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="fa fa-exclamation-triangle text-red-400 text-xl"></i>
                <div>
                    <p class="text-red-400 font-medium mb-2">{{ __('validation.errors_occurred') }}</p>
                    <ul class="list-disc list-inside text-red-300 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Tab Navigation -->
    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl mb-6">
        <div class="flex border-b border-white/10">
            <a href="{{ route('profile.edit') }}" 
               class="px-6 py-4 text-gray-400 hover:text-white transition border-b-2 border-transparent">
                <i class="fa fa-user mr-2"></i>{{ __('profile/edit.tab_profile') }}
            </a>
            <a href="{{ route('profile.subscription') }}" 
               class="px-6 py-4 text-white transition border-b-2 border-blue-500">
                <i class="fa fa-credit-card mr-2"></i>{{ __('profile/edit.tab_subscription') }}
            </a>
            <a href="{{ route('profile.transactions') }}" 
               class="px-6 py-4 text-gray-400 hover:text-white transition border-b-2 border-transparent">
                <i class="fa fa-receipt mr-2"></i>{{ __('profile/edit.tab_transactions') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 {{ Auth::user()->isFree() ? 'lg:grid-cols-2' : '' }} gap-6">
        
        <!-- Membership (Recurring) Section -->
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white">{{ __('subscriptions.membership.title') }}</h2>
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $membershipStatus['status'] === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                    {{ __('subscriptions.membership.status_' . $membershipStatus['status']) }}
                </span>
            </div>

            <p class="text-gray-400 text-sm mb-6">{{ __('subscriptions.membership.explanation') }}</p>

            @if($membershipStatus['tier'] !== 'free')
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between items-center py-3 border-b border-white/10">
                        <span class="text-gray-400">{{ __('subscriptions.membership.current_plan') }}</span>
                        <span class="text-white font-semibold">
                            {{ __('subscriptions.tiers.' . $membershipStatus['tier']) }}
                        </span>
                    </div>

                    @if($membershipStatus['billing_period'])
                        <div class="flex justify-between items-center py-3 border-b border-white/10">
                            <span class="text-gray-400">{{ __('subscriptions.membership.billing_period') }}</span>
                            <span class="text-white">
                                {{ __('subscriptions.membership.' . strtolower($membershipStatus['billing_period'])) }}
                            </span>
                        </div>
                    @endif

                    @if($membershipStatus['next_renewal'])
                        <div class="flex justify-between items-center py-3 border-b border-white/10">
                            <span class="text-gray-400">{{ __('subscriptions.membership.next_renewal') }}</span>
                            <span class="text-white">
                                {{ \Carbon\Carbon::parse($membershipStatus['next_renewal'])->format('M d, Y') }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    @if(Auth::user()->hasRole('admin'))
                        <a href="{{ route('billing.index') }}" 
                           class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg transition font-medium">
                            {{ __('subscriptions.membership.change_plan') }}
                        </a>

                        @if($membershipStatus['is_cancelled'])
                            <form action="{{ route('billing.reactivateSubscription') }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">
                                    {{ __('subscriptions.membership.reactivate_subscription') }}
                                </button>
                            </form>
                        @else
                            <form action="{{ route('billing.cancelSubscription') }}" method="POST" 
                                  onsubmit="return confirm('{{ __('messages.are_you_sure') }}');" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium">
                                    {{ __('subscriptions.membership.cancel_subscription') }}
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-400 mb-4">{{ __('subscriptions.membership.no_active_membership') }}</p>
                    @if(Auth::user()->hasRole('admin'))
                        <a href="{{ route('billing.index') }}" 
                           class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                            {{ __('subscriptions.membership.change_plan') }}
                        </a>
                    @endif
                </div>
            @endif

            <!-- TEST: Quick Plan Switcher -->
            <div class="mt-6 p-4 bg-yellow-900/20 border border-yellow-500/50 rounded-lg">
                <p class="text-yellow-400 text-xs font-semibold mb-3">⚠️ TEST MODE - Quick Plan Switcher</p>
                <form id="testSwitchForm" action="{{ route('profile.test-switch-plan') }}" method="POST" class="flex gap-3 items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-gray-400 text-sm mb-1">Select Plan:</label>
                        <select name="plan_id" id="planSelect" class="w-full bg-[#0d0d0c] border border-white/10 rounded-lg px-3 py-2 text-white">
                            @foreach(\App\Models\PricingPlan::all() as $plan)
                                <option value="{{ $plan->id }}" {{ $membershipStatus['tier'] === strtolower($plan->name) ? 'selected' : '' }}>
                                    {{ $plan->name }} (€{{ number_format($plan->monthly_price_cents / 100, 2) }}/month)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" id="activateBtn" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition font-medium">
                        Activate
                    </button>
                </form>
                <div id="testFeedback" class="mt-3 text-sm hidden"></div>
            </div>
            
            <script>
                document.getElementById('testSwitchForm').addEventListener('submit', function(e) {
                    const btn = document.getElementById('activateBtn');
                    const feedback = document.getElementById('testFeedback');
                    
                    // Show loading state
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>Attivazione...';
                    
                    feedback.className = 'mt-3 text-sm text-blue-400';
                    feedback.textContent = '🔄 Invio richiesta in corso...';
                    feedback.classList.remove('hidden');
                });
            </script>
        </div>

        <!-- Info Note for Advanced/Premium users -->
        @if(!Auth::user()->isFree())
        <div class="bg-[#161615] border border-green-500/30 rounded-2xl shadow-xl p-8">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                        <i class="fa fa-check text-green-400 text-xl"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-2">{{ __('subscriptions.deck_evaluation.included_in_plan') }}</h3>
                    <p class="text-gray-300 mb-3">{{ __('subscriptions.deck_evaluation.advanced_premium_note') }}</p>
                    <a href="{{ route('pokemon.deck-valuation.step1') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">
                        <i class="fa fa-chart-line"></i>
                        {{ __('subscriptions.deck_evaluation.start_evaluation') }}
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Deck Evaluation (One-shot) Section -->
        <!-- Only visible for Free tier users (Advanced/Premium plans include this feature) -->
        @if(Auth::user()->isFree())
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-white mb-2">{{ __('subscriptions.deck_evaluation.title') }}</h2>
            <p class="text-gray-400 text-sm mb-6">{{ __('subscriptions.deck_evaluation.explanation') }}</p>

            @if($activePurchases->isNotEmpty())
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white mb-3">{{ __('subscriptions.deck_evaluation.active_purchases') }}</h3>
                    <div class="space-y-3">
                        @foreach($activePurchases as $purchase)
                            <div class="p-4 bg-black/30 rounded-lg border border-white/10">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-white">{{ $purchase->package->name }}</span>
                                    <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs font-semibold rounded">
                                        Active
                                    </span>
                                </div>
                                
                                <div class="text-sm space-y-1">
                                    <div class="flex justify-between text-gray-400">
                                        <span>{{ __('subscriptions.deck_evaluation.valid_until') }}</span>
                                        <span class="text-white">{{ $purchase->expires_at->format('M d, Y') }}</span>
                                    </div>
                                    
                                    @if($purchase->cards_limit)
                                        <div class="flex justify-between text-gray-400">
                                            <span>{{ __('subscriptions.deck_evaluation.cards_used') }}</span>
                                            <span class="text-white">{{ $purchase->cards_used }} / {{ $purchase->cards_limit }}</span>
                                        </div>
                                        
                                        <!-- Progress bar -->
                                        <div class="mt-2 w-full bg-black/50 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: {{ min(100, ($purchase->cards_used / $purchase->cards_limit) * 100) }}%"></div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 text-purple-400">
                                            <i class="fa fa-infinity"></i>
                                            <span>{{ __('subscriptions.deck_evaluation.unlimited_package') }}</span>
                                        </div>
                                        <p class="text-gray-400 text-xs">{{ __('subscriptions.deck_evaluation.multiple_decks_allowed') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($expiredPurchases->isNotEmpty())
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white mb-3">{{ __('subscriptions.deck_evaluation.expired_purchases') }}</h3>
                    <div class="space-y-2">
                        @foreach($expiredPurchases as $purchase)
                            <div class="p-3 bg-black/20 rounded-lg border border-white/5">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400 text-sm">{{ $purchase->package->name }}</span>
                                    <span class="text-gray-500 text-xs">
                                        {{ __('subscriptions.membership.status_expired') }} {{ $purchase->expires_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($activePurchases->isEmpty() && $expiredPurchases->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-400 mb-4">{{ __('subscriptions.deck_evaluation.no_purchases') }}</p>
                </div>
            @endif

            <div class="flex gap-3 mt-6">
                <a href="{{ route('pokemon.deck-valuation.step1') }}" 
                   class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-center rounded-lg transition font-medium">
                    {{ __('subscriptions.deck_evaluation.go_to_deck_evaluation') }}
                </a>
                <a href="{{ route('deck-evaluation.packages.index') }}" 
                   class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg transition font-medium">
                    {{ __('subscriptions.deck_evaluation.purchase_package') }}
                </a>
            </div>
        </div>
        @endif

    </div>

    <!-- Coexistence Note -->
    <!-- Only shown for Free tier users who can see Deck Evaluation section -->
    @if(Auth::user()->isFree())
    <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
        <div class="flex items-start gap-3">
            <i class="fa fa-info-circle text-blue-400 mt-1"></i>
            <p class="text-blue-300 text-sm">{{ __('subscriptions.deck_evaluation.coexistence_note') }}</p>
        </div>
    </div>
    @endif

</div>
@endsection
