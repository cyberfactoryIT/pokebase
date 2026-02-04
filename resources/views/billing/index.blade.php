@extends('layouts.app')

@section('content')
<style>
    html {
        scroll-behavior: smooth;
    }
</style>

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
                            <a href="#available-plans" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold">
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
                            <div class="text-gray-400 text-xs mb-1">
                                @if($membershipStatus['is_cancelled'])
                                    {{ __('subscriptions.membership.expires_on') }}
                                @else
                                    {{ __('subscriptions.membership.next_renewal') }}
                                @endif
                            </div>
                            <div class="text-white font-medium">{{ $membershipStatus['next_renewal']->format('M d, Y') }}</div>
                            @php
                                $daysUntilRenewal = round(now()->diffInDays($membershipStatus['next_renewal'], false));
                            @endphp
                            @if($daysUntilRenewal >= 0)
                            <div class="text-gray-400 text-xs mt-1">
                                @if($membershipStatus['is_cancelled'])
                                    @if($daysUntilRenewal == 0)
                                        {{ __('subscriptions.membership.expires_today') }}
                                    @elseif($daysUntilRenewal == 1)
                                        {{ __('subscriptions.membership.valid_for_1_day') }}
                                    @else
                                        {{ __('subscriptions.membership.valid_for_days', ['days' => $daysUntilRenewal]) }}
                                    @endif
                                @else
                                    @if($daysUntilRenewal == 0)
                                        {{ __('subscriptions.membership.renews_today') }}
                                    @elseif($daysUntilRenewal == 1)
                                        {{ __('subscriptions.membership.renews_tomorrow') }}
                                    @else
                                        {{ __('subscriptions.membership.renews_in_days', ['days' => $daysUntilRenewal]) }}
                                    @endif
                                @endif
                            </div>
                            @endif
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

            <!-- Billing Information Card -->
            <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6" x-data="{ editBilling: false }">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-white mb-1">
                            <i class="fas fa-file-invoice text-purple-400 mr-2"></i>
                            {{ __('subscriptions.billing_info.title', [], 'Billing Information') }}
                        </h2>
                        <p class="text-sm text-gray-400">{{ __('subscriptions.billing_info.subtitle', [], 'Manage your billing details and company information') }}</p>
                    </div>
                    @if(Auth::user()->hasRole('admin'))
                    <button @click="editBilling = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm font-medium">
                        <i class="fas fa-edit mr-2"></i>
                        {{ __('subscriptions.billing_info.edit', [], 'Edit') }}
                    </button>
                    @endif
                </div>

                @php
                    $org = Auth::user()->organization;
                @endphp

                @if($org)
                    <!-- Display Mode -->
                    <div x-show="!editBilling">
                        <div class="grid md:grid-cols-2 gap-4">
                            <!-- Company Name -->
                            <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-blue-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-building text-blue-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-gray-400 text-xs mb-1">{{ __('subscriptions.billing_info.company', [], 'Company') }}</div>
                                        <div class="text-white font-medium truncate">{{ $org->company ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Billing Email -->
                            <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-green-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-envelope text-green-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-gray-400 text-xs mb-1">{{ __('subscriptions.billing_info.billing_email', [], 'Billing Email') }}</div>
                                        <div class="text-white font-medium truncate">{{ $org->billing_email ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- VAT Number -->
                            <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-purple-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-hashtag text-purple-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-gray-400 text-xs mb-1">{{ __('subscriptions.billing_info.vat', [], 'VAT Number') }}</div>
                                        <div class="text-white font-medium truncate">{{ $org->vat_number ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Country -->
                            <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-orange-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-globe text-orange-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-gray-400 text-xs mb-1">{{ __('subscriptions.billing_info.country', [], 'Country') }}</div>
                                        <div class="text-white font-medium truncate">{{ $org->country ?: '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="mt-4 bg-white/5 border border-white/10 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-red-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-red-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-gray-400 text-xs mb-2">{{ __('subscriptions.billing_info.address', [], 'Address') }}</div>
                                    <div class="text-white space-y-1">
                                        @if($org->address_line1)
                                            <div>{{ $org->address_line1 }}</div>
                                        @endif
                                        @if($org->address_line2)
                                            <div>{{ $org->address_line2 }}</div>
                                        @endif
                                        @if($org->city || $org->postcode)
                                            <div>{{ $org->postcode }} {{ $org->city }}</div>
                                        @endif
                                        @if(!$org->address_line1 && !$org->address_line2 && !$org->city)
                                            <div class="text-gray-500">-</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <form x-show="editBilling" x-cloak method="POST" action="{{ route('billing.updateBillingInfo') }}" class="space-y-4">
                        @csrf
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <!-- Company -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    <i class="fas fa-building text-blue-400 mr-1"></i>
                                    {{ __('subscriptions.billing_info.company', [], 'Company') }}
                                </label>
                                <input type="text" name="company" value="{{ old('company', $org->company) }}" 
                                    class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                            </div>

                            <!-- Billing Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    <i class="fas fa-envelope text-green-400 mr-1"></i>
                                    {{ __('subscriptions.billing_info.billing_email', [], 'Billing Email') }}
                                </label>
                                <input type="email" name="billing_email" value="{{ old('billing_email', $org->billing_email) }}" 
                                    class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                            </div>

                            <!-- VAT Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    <i class="fas fa-hashtag text-purple-400 mr-1"></i>
                                    {{ __('subscriptions.billing_info.vat', [], 'VAT Number') }}
                                </label>
                                <input type="text" name="vat_number" value="{{ old('vat_number', $org->vat_number) }}" 
                                    class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                            </div>

                            <!-- Country -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    <i class="fas fa-globe text-orange-400 mr-1"></i>
                                    {{ __('subscriptions.billing_info.country', [], 'Country') }}
                                </label>
                                <select name="country" class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                                    <option value="">{{ __('subscriptions.billing_info.select_country', [], 'Select a country') }}</option>
                                    @php
                                        $countries = __('countries');
                                        $denmarkKey = 'DK';
                                        $denmark = [$denmarkKey => $countries[$denmarkKey]];
                                        unset($countries[$denmarkKey]);
                                        
                                        // Sort remaining countries by translated name
                                        uasort($countries, function($a, $b) {
                                            return strcasecmp($a, $b);
                                        });
                                        
                                        // Merge Denmark first, then alphabetically sorted countries
                                        $sortedCountries = $denmark + $countries;
                                    @endphp
                                    
                                    @foreach($sortedCountries as $key => $name)
                                        <option value="{{ $key }}" {{ old('country', $org->country) == $key ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Address Fields -->
                        <div class="space-y-4 pt-4 border-t border-white/10">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    <i class="fas fa-map-marker-alt text-red-400 mr-1"></i>
                                    {{ __('subscriptions.billing_info.address', [], 'Address') }} 1
                                </label>
                                <input type="text" name="address_line1" value="{{ old('address_line1', $org->address_line1) }}" 
                                    class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    {{ __('subscriptions.billing_info.address', [], 'Address') }} 2
                                </label>
                                <input type="text" name="address_line2" value="{{ old('address_line2', $org->address_line2) }}" 
                                    class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <!-- Postcode -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        {{ __('subscriptions.billing_info.postcode', [], 'Postcode') }}
                                    </label>
                                    <input type="text" name="postcode" value="{{ old('postcode', $org->postcode) }}" 
                                        class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                                </div>

                                <!-- City -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        {{ __('subscriptions.billing_info.city', [], 'City') }}
                                    </label>
                                    <input type="text" name="city" value="{{ old('city', $org->city) }}" 
                                        class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-4 border-t border-white/10">
                            <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold">
                                <i class="fas fa-save mr-2"></i>
                                {{ __('subscriptions.billing_info.save', [], 'Save Changes') }}
                            </button>
                            <button type="button" @click="editBilling = false" class="px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-gray-300 rounded-lg transition font-semibold">
                                <i class="fas fa-times mr-2"></i>
                                {{ __('subscriptions.billing_info.cancel', [], 'Cancel') }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-white/5 border border-white/10 rounded-lg p-6 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-500/20 rounded-full mb-4">
                            <i class="fas fa-building text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ __('subscriptions.billing_info.no_org', [], 'No Organization') }}</h3>
                        <p class="text-gray-400 text-sm">{{ __('subscriptions.billing_info.no_org_desc', [], 'No organization associated with your account') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Available Plans Section -->
        <div id="available-plans" class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-white mb-2">
                        <i class="fas fa-layer-group text-blue-400 mr-2"></i>
                        {{ __('subscriptions.available_plans', [], 'Available Plans') }}
                    </h2>
                    <p class="text-gray-400">{{ __('subscriptions.available_plans_subtitle', [], 'Choose the plan that fits your needs') }}</p>
                    <p class="text-sm text-gray-500 mt-2">
                        * {{ __('pages.pricing_currency_note') }}
                    </p>
                </div>
                
                <!-- Billing Period Toggle -->
                <div class="flex items-center gap-3 bg-[#161615] border border-white/15 rounded-xl p-2">
                    <button onclick="switchBillingPeriod('monthly')" id="btn-monthly" class="px-4 py-2 rounded-lg text-sm font-medium transition bg-blue-600 text-white">
                        {{ __('subscriptions.membership.monthly') }}
                    </button>
                    <button onclick="switchBillingPeriod('yearly')" id="btn-yearly" class="px-4 py-2 rounded-lg text-sm font-medium transition text-gray-400 hover:text-white">
                        {{ __('subscriptions.membership.yearly') }}
                        <span class="ml-1 px-2 py-0.5 bg-green-600/20 text-green-400 text-xs rounded-full">-17%</span>
                    </button>
                </div>
            </div>

            <!-- Pricing Cards Grid -->
            <div class="grid md:grid-cols-3 gap-6">
                @php
                    $pricingPlans = \App\Models\PricingPlan::orderBy('monthly_price_cents')->get();
                    $currentTier = $membershipStatus['tier'];
                @endphp

                @foreach($pricingPlans as $plan)
                @php
                    $isCurrentPlan = strtolower($plan->name) === $currentTier;
                    $monthlyPrice = $plan->monthly_price_cents / 100;
                    $yearlyPrice = $plan->yearly_price_cents / 100;
                    $currency = $plan->currency === 'DKK' ? 'kr.' : '€';
                    $savings = $yearlyPrice > 0 ? round((1 - ($yearlyPrice / 12) / $monthlyPrice) * 100) : 0;
                    
                    // Determine if this is an upgrade or downgrade
                    $tierOrder = ['free' => 0, 'advanced' => 1, 'premium' => 2];
                    $currentOrder = $tierOrder[$currentTier] ?? 0;
                    $planOrder = $tierOrder[strtolower($plan->code)] ?? 0;
                    $isUpgrade = $planOrder > $currentOrder;
                    $isDowngrade = $planOrder < $currentOrder;
                    
                    // Color scheme per plan
                    $colors = [
                        'free' => ['accent' => 'gray', 'border' => 'border-gray-500/30', 'bg' => 'from-gray-900/30 to-gray-800/20'],
                        'advanced' => ['accent' => 'blue', 'border' => 'border-blue-500/30', 'bg' => 'from-blue-900/30 to-blue-800/20'],
                        'premium' => ['accent' => 'purple', 'border' => 'border-purple-500/30', 'bg' => 'from-purple-900/30 to-purple-800/20'],
                    ];
                    $color = $colors[$plan->code] ?? $colors['advanced'];
                @endphp

                <div class="bg-[#161615] border {{ $isCurrentPlan ? 'border-2 ' . $color['border'] . ' ring-2 ring-' . $color['accent'] . '-500/20' : 'border-white/15' }} rounded-2xl shadow-xl overflow-hidden {{ $isCurrentPlan ? 'relative' : '' }}">
                    
                    <!-- Current Plan Badge -->
                    @if($isCurrentPlan)
                    <div class="absolute top-4 right-4 px-3 py-1 bg-{{ $color['accent'] }}-600/20 border border-{{ $color['accent'] }}-500/30 text-{{ $color['accent'] }}-300 rounded-full text-xs font-semibold">
                        <i class="fas fa-check-circle mr-1"></i>
                        Current Plan
                    </div>
                    @endif

                    <div class="p-6">
                        <!-- Plan Header -->
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-white mb-1">
                                @if($plan->code === 'free')
                                    {{ __('home/pricing.free_title') }}
                                @elseif($plan->code === 'advanced')
                                    {{ __('home/pricing.pro_title') }}
                                @else
                                    {{ __('home/pricing.enterprise_title') }}
                                @endif
                            </h3>
                            <p class="text-sm text-gray-400 mb-3">
                                @if($plan->code === 'free')
                                    {{ __('home/pricing.free_tagline') }}
                                @elseif($plan->code === 'advanced')
                                    {{ __('home/pricing.pro_tagline') }}
                                @else
                                    {{ __('home/pricing.enterprise_tagline') }}
                                @endif
                            </p>
                            
                            <!-- Monthly Price -->
                            <div class="price-monthly">
                                <div class="flex items-baseline gap-2 mb-1">
                                    <span class="text-4xl font-bold text-{{ $color['accent'] }}-400">
                                        {{ $monthlyPrice == 0 ? '0' : number_format($monthlyPrice, 2) }}
                                    </span>
                                    <span class="text-xl text-gray-400">{{ $currency }}</span>
                                    <span class="text-sm text-gray-500">/month</span>
                                </div>
                            </div>

                            <!-- Yearly Price (Hidden by default) -->
                            @if($yearlyPrice > 0)
                            <div class="price-yearly hidden">
                                <div class="flex items-baseline gap-2 mb-1">
                                    <span class="text-4xl font-bold text-{{ $color['accent'] }}-400">{{ number_format($yearlyPrice, 0) }}</span>
                                    <span class="text-xl text-gray-400">{{ $currency }}</span>
                                    <span class="text-sm text-gray-500">/year</span>
                                </div>
                                <div class="text-sm text-green-400">Save {{ $savings }}%</div>
                            </div>
                            @endif
                        </div>

                        <!-- Features -->
                        <ul class="space-y-3 mb-6 min-h-[200px]">
                            @php
                                // Determine number of features per plan
                                $featureCount = match($plan->code) {
                                    'free' => 4,
                                    'advanced' => 5,
                                    'premium' => 7,
                                    default => 4
                                };
                                
                                $featurePrefix = match($plan->code) {
                                    'free' => 'free_',
                                    'advanced' => 'pro_',
                                    'premium' => 'enterprise_',
                                    default => 'free_'
                                };
                            @endphp
                            
                            @for($i = 1; $i <= $featureCount; $i++)
                            <li class="flex items-start text-sm">
                                <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-300">{{ __('home/pricing.' . $featurePrefix . 'feat' . $i) }}</span>
                            </li>
                            @endfor
                        </ul>

                        <!-- Action Button -->
                        @if($isCurrentPlan)
                            <button disabled class="w-full px-6 py-3 bg-{{ $color['accent'] }}-600/20 border border-{{ $color['accent'] }}-500/30 text-{{ $color['accent'] }}-300 rounded-lg font-semibold cursor-not-allowed">
                                <i class="fas fa-check mr-2"></i>
                                Current Plan
                            </button>
                        @elseif($isUpgrade)
                            <a href="{{ route('checkout.show', ['plan_id' => $plan->id, 'billing_period' => 'monthly']) }}" 
                               class="upgrade-button w-full px-6 py-3 bg-gradient-to-r from-{{ $color['accent'] }}-600 to-{{ $color['accent'] }}-700 hover:from-{{ $color['accent'] }}-700 hover:to-{{ $color['accent'] }}-800 text-white rounded-lg font-semibold transition shadow-lg inline-block text-center"
                               data-plan-id="{{ $plan->id }}">
                                <i class="fas fa-arrow-up mr-2"></i>
                                Upgrade to {{ $plan->name }}
                            </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Invoices Section -->
        @if(Auth::user()->organization)
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-file-invoice text-green-400 mr-2"></i>
                    {{ __('billing.invoices.title') }}
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
                            <th class="text-left text-gray-400 text-sm font-medium py-3 px-4">{{ __('billing.invoices.invoice_number') }}</th>
                            <th class="text-left text-gray-400 text-sm font-medium py-3 px-4">{{ __('billing.invoices.date') }}</th>
                            <th class="text-left text-gray-400 text-sm font-medium py-3 px-4">{{ __('billing.invoices.amount') }}</th>
                            <th class="text-left text-gray-400 text-sm font-medium py-3 px-4">{{ __('billing.invoices.status') }}</th>
                            <th class="text-right text-gray-400 text-sm font-medium py-3 px-4">{{ __('billing.invoices.actions') }}</th>
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
                                <span class="text-white font-semibold">{{ number_format($invoice->total_cents / 100, 2) }} {{ strtoupper($invoice->currency) }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($invoice->status === 'paid')
                                    <span class="px-2 py-1 bg-green-600/20 border border-green-500/30 text-green-300 rounded text-xs font-medium">{{ __('billing.invoices.status_paid') }}</span>
                                @elseif($invoice->status === 'open')
                                    <span class="px-2 py-1 bg-yellow-600/20 border border-yellow-500/30 text-yellow-300 rounded text-xs font-medium">{{ __('billing.invoices.status_open') }}</span>
                                @else
                                    <span class="px-2 py-1 bg-red-600/20 border border-red-500/30 text-red-300 rounded text-xs font-medium">{{ ucfirst($invoice->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('billing.invoice.show', $invoice) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/30 text-blue-300 rounded-lg text-xs font-medium transition">
                                    <i class="fas fa-eye"></i>
                                    <span>{{ __('billing.invoices.view') }}</span>
                                </a>
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
                <p class="text-gray-400">{{ __('billing.invoices.no_invoices') }}</p>
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
                                    @php
                                        $planCurrency = $plan->currency === 'DKK' ? 'kr.' : '€';
                                    @endphp
                                    <option value="{{ $plan->id }}" 
                                            data-monthly="{{ number_format($plan->monthly_price_cents / 100, 2) }}" 
                                            data-yearly="{{ number_format($plan->yearly_price_cents / 100, 2) }}"
                                            data-currency="{{ $planCurrency }}"
                                            {{ strtolower($plan->name) === $membershipStatus['tier'] ? 'disabled' : '' }}>
                                        {{ $plan->name }} - {{ number_format($plan->monthly_price_cents / 100, 2) }} {{ $planCurrency }}/month
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
            const currency = opt.getAttribute('data-currency') || 'kr.';
            const planName = opt.textContent.split(' - ')[0];
            const current = opt.textContent.includes('(Current)') ? ' (Current)' : '';
            opt.textContent = `${planName} - ${price} ${currency}/${period === 'yearly' ? 'year' : 'month'}${current}`;
        });
    }
    
    periodSelect.addEventListener('change', updatePlanLabels);
});

// Switch billing period (monthly/yearly) in pricing cards
function switchBillingPeriod(period) {
    const btnMonthly = document.getElementById('btn-monthly');
    const btnYearly = document.getElementById('btn-yearly');
    const monthlyPrices = document.querySelectorAll('.price-monthly');
    const yearlyPrices = document.querySelectorAll('.price-yearly');
    const billingInputs = document.querySelectorAll('.billing-period-input');
    const upgradeButtons = document.querySelectorAll('.upgrade-button');
    
    if (period === 'monthly') {
        // Update button styles
        btnMonthly.classList.add('bg-blue-600', 'text-white');
        btnMonthly.classList.remove('text-gray-400', 'hover:text-white');
        btnYearly.classList.remove('bg-blue-600', 'text-white');
        btnYearly.classList.add('text-gray-400', 'hover:text-white');
        
        // Show monthly, hide yearly
        monthlyPrices.forEach(el => el.classList.remove('hidden'));
        yearlyPrices.forEach(el => el.classList.add('hidden'));
        
        // Update hidden inputs (for downgrade buttons)
        billingInputs.forEach(input => input.value = 'monthly');
        
        // Update upgrade button links
        upgradeButtons.forEach(btn => {
            const planId = btn.getAttribute('data-plan-id');
            btn.href = `{{ route('checkout.show') }}?plan_id=${planId}&billing_period=monthly`;
        });
    } else {
        // Update button styles
        btnYearly.classList.add('bg-blue-600', 'text-white');
        btnYearly.classList.remove('text-gray-400', 'hover:text-white');
        btnMonthly.classList.remove('bg-blue-600', 'text-white');
        btnMonthly.classList.add('text-gray-400', 'hover:text-white');
        
        // Show yearly, hide monthly
        monthlyPrices.forEach(el => el.classList.add('hidden'));
        yearlyPrices.forEach(el => el.classList.remove('hidden'));
        
        // Update hidden inputs (for downgrade buttons)
        billingInputs.forEach(input => input.value = 'yearly');
        
        // Update upgrade button links
        upgradeButtons.forEach(btn => {
            const planId = btn.getAttribute('data-plan-id');
            btn.href = `{{ route('checkout.show') }}?plan_id=${planId}&billing_period=yearly`;
        });
    }
}
</script>
@endsection
