@extends('layouts.public')

@section('title', __('meta.pricing_title'))
@section('description', __('meta.pricing_description'))

@section('content')
<div class="min-h-screen bg-[#1a1a1a] text-white py-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">{{ __('pages.pricing_title') }}</h1>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">{{ __('pages.pricing_subtitle') }}</p>
            
            <!-- Billing Toggle -->
            @php
                $hasYearlyPricing = $plans->where('yearly_price_cents', '>', 0)->count() > 0;
            @endphp
            @if($hasYearlyPricing)
            <div class="flex items-center justify-center gap-4 mt-8">
                <span class="text-gray-400" id="monthly-label">{{ __('pages.pricing_monthly') }}</span>
                <button onclick="toggleBilling()" class="relative inline-flex h-8 w-14 items-center rounded-full bg-white/10 transition-colors focus:outline-none" id="billing-toggle">
                    <span class="inline-block h-6 w-6 transform rounded-full bg-blue-500 transition-transform translate-x-1" id="toggle-dot"></span>
                </button>
                <span class="text-gray-400" id="yearly-label">{{ __('pages.pricing_yearly') }}</span>
                <span class="ml-2 px-3 py-1 bg-green-600/20 text-green-400 text-sm font-semibold rounded-full">{{ __('pages.pricing_save_17') }}</span>
            </div>
            @endif
        </div>

        <!-- Pricing Cards -->
        <!-- Currency Note -->
        <div class="text-center mb-8">
            <p class="text-sm text-gray-500">
                * {{ __('pages.pricing_currency_note') }}
            </p>
        </div>

        <!-- Pricing Cards -->
        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-24">
            @php
                $planOrder = ['free', 'advanced', 'premium'];
                $orderedPlans = collect($planOrder)->map(function($code) use ($plans) {
                    return $plans->firstWhere('code', $code);
                })->filter();
                
                $taglines = [
                    'free' => __('pages.pricing_tagline_free'),
                    'advanced' => __('pages.pricing_tagline_advanced'),
                    'premium' => __('pages.pricing_tagline_premium'),
                ];
                
                $userTier = auth()->check() ? auth()->user()->subscriptionTier() : null;
            @endphp

            @foreach($orderedPlans as $plan)
            @php
                $isRecommended = $plan->code === 'advanced';
                $monthlyPrice = $plan->monthly_price_cents / 100;
                $yearlyPrice = $plan->yearly_price_cents ? $plan->yearly_price_cents / 100 : 0;
                $currency = $plan->currency === 'DKK' ? 'kr.' : '€';
                $savings = $yearlyPrice > 0 && $monthlyPrice > 0 ? round((1 - ($yearlyPrice / 12) / $monthlyPrice) * 100) : 0;
                
                $colors = [
                    'free' => ['accent' => 'green', 'from' => 'from-green-600', 'to' => 'to-green-700'],
                    'advanced' => ['accent' => 'blue', 'from' => 'from-blue-600', 'to' => 'to-purple-600'],
                    'premium' => ['accent' => 'purple', 'from' => 'from-purple-600', 'to' => 'to-pink-600'],
                ];
                $color = $colors[$plan->code] ?? $colors['advanced'];
                
                $isCurrentPlan = $userTier === $plan->code;
            @endphp

            <div class="relative bg-[#1a1a1a] border {{ $isRecommended ? 'border-2 border-blue-500' : 'border border-white/10' }} rounded-2xl p-8 hover:border-{{ $color['accent'] }}-500/50 transition-all {{ $isRecommended ? 'transform md:scale-105 shadow-2xl shadow-blue-500/20' : '' }}">
                
                @if($isRecommended)
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-gradient-to-r {{ $color['from'] }} {{ $color['to'] }} rounded-full text-sm font-bold shadow-lg">
                    {{ __('pages.pricing_recommended') }}
                </div>
                @endif

                <div class="text-center mb-8 {{ $isRecommended ? 'mt-2' : '' }}">
                    <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                    <p class="text-gray-400 text-sm mb-6">{{ $taglines[$plan->code] ?? '' }}</p>
                    
                    <!-- Monthly Price -->
                    <div class="price-monthly">
                        <div class="text-5xl font-bold mb-2">
                            @if($monthlyPrice == 0)
                                <span class="text-{{ $color['accent'] }}-400">Free</span>
                            @else
                                <span class="text-{{ $color['accent'] }}-400">{{ number_format($monthlyPrice, 2, ',', '.') }}</span>
                                <span class="text-2xl text-gray-400"> {{ $currency }}</span>
                            @endif
                        </div>
                        @if($monthlyPrice > 0)
                        <div class="text-gray-400 text-sm">{{ __('pages.pricing_per_month') }}</div>
                        @endif
                    </div>

                    <!-- Yearly Price -->
                    @if($yearlyPrice > 0)
                    <div class="price-yearly hidden">
                        <div class="text-5xl font-bold mb-2">
                            <span class="text-{{ $color['accent'] }}-400">{{ number_format($yearlyPrice, 0, ',', '.') }}</span>
                            <span class="text-2xl text-gray-400"> {{ $currency }}</span>
                        </div>
                        <div class="text-gray-400 text-sm">
                            {{ __('pages.pricing_per_year') }}
                            @if($savings > 0)
                            <span class="text-green-400 font-semibold ml-2">({{ __('pages.pricing_save') }} {{ $savings }}%)</span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Key features list (top highlights) -->
                <ul class="space-y-3 mb-8">
                    @if($plan->code === 'free')
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_catalog') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_single_card_price') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_1_game') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_100_cards') }}</span>
                        </li>
                    @elseif($plan->code === 'advanced')
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_full_price_overview') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_statistics') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_3_games') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_unlimited_cards') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_share_1_deck') }}</span>
                        </li>
                    @else
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_everything_advanced') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_advanced_import') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_all_games') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_full_sharing') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_real_photos') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_showcase_selling') }}</span>
                        </li>
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-gray-300">{{ __('pages.pricing_feat_vip_groups') }}</span>
                        </li>
                    @endif
                </ul>

                <!-- CTA Button -->
                @guest
                    @if($monthlyPrice == 0)
                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 bg-white/10 hover:bg-white/20 rounded-xl transition font-semibold text-lg">
                            {{ __('pages.pricing_cta_free') }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 {{ $isRecommended ? 'bg-gradient-to-r ' . $color['from'] . ' ' . $color['to'] . ' hover:opacity-90 shadow-lg' : 'bg-white/10 hover:bg-white/20' }} rounded-xl transition font-semibold text-lg">
                            {{ __('pages.pricing_cta_upgrade') }}
                        </a>
                    @endif
                @else
                    @if($isCurrentPlan)
                        <div class="block w-full text-center px-6 py-4 bg-gray-600/50 rounded-xl font-semibold text-lg cursor-default">
                            {{ __('pages.pricing_current_plan') }}
                        </div>
                    @elseif($monthlyPrice == 0)
                        <div class="block w-full text-center px-6 py-4 bg-white/10 rounded-xl font-semibold text-lg text-gray-500 cursor-default">
                            {{ __('pages.pricing_downgrade_unavailable') }}
                        </div>
                    @else
                        <a href="{{ route('billing.index') }}?upgrade={{ $plan->code }}" class="block w-full text-center px-6 py-4 {{ $isRecommended ? 'bg-gradient-to-r ' . $color['from'] . ' ' . $color['to'] . ' hover:opacity-90 shadow-lg' : 'bg-white/10 hover:bg-white/20' }} rounded-xl transition font-semibold text-lg">
                            {{ __('pages.pricing_upgrade_to', ['plan' => $plan->name]) }}
                        </a>
                    @endif
                @endguest
            </div>
            @endforeach
        </div>

        <!-- Feature Comparison Matrix -->
        <div class="mb-24">
            <h2 class="text-3xl font-bold text-center mb-12">{{ __('pages.pricing_compare_plans') }}</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th scope="col" class="text-left p-4 font-semibold text-gray-300">{{ __('pages.pricing_feature') }}</th>
                            <th scope="col" class="text-center p-4 font-semibold">Free</th>
                            <th scope="col" class="text-center p-4 font-semibold text-blue-400">Advanced</th>
                            <th scope="col" class="text-center p-4 font-semibold text-purple-400">Premium</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_catalog') }}</th>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-green-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-blue-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_single_card_price') }}</th>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-green-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-blue-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_collection_deck_value') }}</th>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-blue-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_statistics') }}</th>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-blue-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_active_games') }}</th>
                            <td class="text-center p-4"><span class="text-gray-300">1</span></td>
                            <td class="text-center p-4"><span class="text-gray-300">{{ __('pages.pricing_up_to_3') }}</span></td>
                            <td class="text-center p-4"><span class="text-purple-400 font-semibold">{{ __('pages.pricing_all') }}</span></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_cards_limit') }}</th>
                            <td class="text-center p-4"><span class="text-gray-300">100</span></td>
                            <td class="text-center p-4"><span class="text-blue-400 font-semibold">{{ __('pages.pricing_unlimited') }}</span></td>
                            <td class="text-center p-4"><span class="text-purple-400 font-semibold">{{ __('pages.pricing_unlimited') }}</span></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_deck_sharing') }}</th>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><span class="text-gray-300">{{ __('pages.pricing_1_deck') }}</span></td>
                            <td class="text-center p-4"><span class="text-purple-400 font-semibold">{{ __('pages.pricing_all_decks') }}</span></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_collection_card_sharing') }}</th>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_advanced_import') }}</th>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_real_photos') }}</th>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_showcase_selling') }}</th>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                        <tr class="border-b border-white/10 hover:bg-white/5">
                            <th scope="row" class="text-left p-4 font-normal text-gray-300">{{ __('pages.pricing_feat_vip_groups') }}</th>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><span class="text-gray-500">–</span></td>
                            <td class="text-center p-4"><svg class="w-5 h-5 text-purple-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expanded Plan Descriptions -->
        <div class="mb-24 max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">{{ __('pages.pricing_which_plan') }}</h2>
            
            <div class="space-y-8">
                <div class="bg-[#1a1a1a] border border-white/10 rounded-xl p-8 hover:border-green-500/50 transition-all">
                    <h3 class="text-2xl font-bold text-green-400 mb-4">Free</h3>
                    <p class="text-gray-300 leading-relaxed">
                        {{ __('pages.pricing_free_desc') }}
                    </p>
                </div>
                
                <div class="bg-[#1a1a1a] border border-blue-500 rounded-xl p-8 hover:border-blue-400 transition-all shadow-xl shadow-blue-500/10">
                    <h3 class="text-2xl font-bold text-blue-400 mb-4">Advanced</h3>
                    <p class="text-gray-300 leading-relaxed">
                        {{ __('pages.pricing_advanced_desc') }}
                    </p>
                </div>
                
                <div class="bg-[#1a1a1a] border border-white/10 rounded-xl p-8 hover:border-purple-500/50 transition-all">
                    <h3 class="text-2xl font-bold text-purple-400 mb-4">Premium</h3>
                    <p class="text-gray-300 leading-relaxed">
                        {{ __('pages.pricing_premium_desc') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- FAQ / Enterprise Section -->
        <!-- FAQ / Enterprise Section -->
        <div class="text-center bg-gradient-to-br from-blue-900/10 to-purple-900/10 rounded-2xl p-12">
            <h2 class="text-3xl font-bold mb-4">{{ __('pages.pricing_enterprise_title') }}</h2>
            <p class="text-xl text-gray-400 mb-8">
                {{ __('pages.pricing_enterprise_text') }}
            </p>
            <a href="{{ route('contact') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-xl font-semibold text-lg transition shadow-lg">
                {{ __('pages.pricing_enterprise_link') }}
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
    let isYearly = false;

    function toggleBilling() {
        isYearly = !isYearly;
        const toggle = document.getElementById('billing-toggle');
        const dot = document.getElementById('toggle-dot');
        const monthlyPrices = document.querySelectorAll('.price-monthly');
        const yearlyPrices = document.querySelectorAll('.price-yearly');
        
        if (isYearly) {
            dot.classList.remove('translate-x-1');
            dot.classList.add('translate-x-7');
            toggle.classList.add('bg-blue-600');
            toggle.classList.remove('bg-white/10');
            monthlyPrices.forEach(el => el.classList.add('hidden'));
            yearlyPrices.forEach(el => el.classList.remove('hidden'));
        } else {
            dot.classList.remove('translate-x-7');
            dot.classList.add('translate-x-1');
            toggle.classList.remove('bg-blue-600');
            toggle.classList.add('bg-white/10');
            monthlyPrices.forEach(el => el.classList.remove('hidden'));
            yearlyPrices.forEach(el => el.classList.add('hidden'));
        }
    }
</script>

@endsection
