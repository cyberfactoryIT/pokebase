<!-- Pricing Teaser Section -->
<section class="py-24 px-6 lg:px-8 bg-gradient-to-br from-blue-900/10 to-purple-900/10">
    <div class="max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-block px-4 py-2 bg-green-600/20 rounded-full text-green-400 text-sm font-semibold mb-4">
                {{ __('home/pricing.badge') }}
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                {{ __('home/pricing.title') }}
            </h2>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                {{ __('home/pricing.subtitle') }}
            </p>
        </div>

        <!-- Pricing Cards (Preview) -->
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            @php
                $plans = \App\Models\PricingPlan::orderBy('monthly_price_cents')->get();
                $freePlan = $plans->where('code', 'free')->first();
                $advancedPlan = $plans->where('code', 'advanced')->first();
                $premiumPlan = $plans->where('code', 'premium')->first();
            @endphp

            @if($freePlan)
            <!-- Free Plan -->
            <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl p-8 hover:border-white/20 transition">
                <div class="text-center mb-6 min-h-[160px]">
                    <h3 class="text-2xl font-bold mb-1">{{ __('home/pricing.free_title') }}</h3>
                    <p class="text-sm text-gray-400 mb-3">{{ __('home/pricing.free_tagline') }}</p>
                    <div class="text-4xl font-bold mb-1">
                        <span class="text-green-400">{{ __('home/pricing.free_price') }}</span>
                    </div>
                    <div class="text-gray-400 text-sm">{{ __('home/pricing.free_period') }}</div>
                </div>
                
                <ul class="space-y-3 mb-8 min-h-[140px]">
                    @foreach(['feat1', 'feat2', 'feat3', 'feat4'] as $feat)
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ __('home/pricing.free_' . $feat) }}</span>
                    </li>
                    @endforeach
                </ul>
                
                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 bg-white/10 hover:bg-white/20 rounded-lg transition font-semibold">
                    {{ __('home/pricing.free_cta') }}
                </a>
            </div>
            @endif

            @if($advancedPlan)
            <!-- Pro Plan (Featured) -->
            <div class="bg-gradient-to-br from-blue-600/20 to-purple-600/20 border-2 border-purple-500 rounded-2xl p-8 transform md:scale-105 relative">
                <!-- Popular Badge -->
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full text-sm font-bold">
                    {{ __('home/pricing.pro_badge') }}
                </div>
                
                <div class="text-center mb-6 mt-2 min-h-[160px]">
                    <h3 class="text-2xl font-bold mb-1">{{ __('home/pricing.pro_title') }}</h3>
                    <p class="text-sm text-gray-300 mb-3">{{ __('home/pricing.pro_tagline') }}</p>
                    <div class="text-4xl font-bold mb-1">
                        @php
                            $monthlyPrice = $advancedPlan->monthly_price_cents / 100;
                            $currency = $advancedPlan->currency === 'DKK' ? 'kr' : '€';
                        @endphp
                        <span class="text-blue-400">{{ number_format($monthlyPrice, 2, ',', '.') }} {{ $currency }}</span>
                        <span class="text-xl text-gray-400">/{{ __('home/pricing.pro_period') }}</span>
                    </div>
                    @if($advancedPlan->yearly_price_cents)
                        @php
                            $yearlyPrice = $advancedPlan->yearly_price_cents / 100;
                            $monthlySavings = round((1 - ($yearlyPrice / 12) / $monthlyPrice) * 100);
                        @endphp
                        <div class="text-gray-400 text-sm">
                            {{ __('home/pricing.pro_subtitle_dynamic', [
                                'yearly_price' => number_format($yearlyPrice, 0, ',', '.'),
                                'currency' => $currency,
                                'savings' => $monthlySavings
                            ]) }}
                        </div>
                    @else
                        <div class="text-gray-400 text-sm">{{ __('home/pricing.pro_subtitle') }}</div>
                    @endif
                </div>
                
                <ul class="space-y-3 mb-8 min-h-[140px]">
                    @foreach(['feat1', 'feat2', 'feat3', 'feat4', 'feat5'] as $feat)
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ __('home/pricing.pro_' . $feat) }}</span>
                    </li>
                    @endforeach
                </ul>
                
                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg transition font-semibold shadow-lg">
                    {{ __('home/pricing.pro_cta') }}
                </a>
            </div>
            @endif

            @if($premiumPlan)
            <!-- Enterprise/Premium -->
            <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl p-8 hover:border-white/20 transition">
                <div class="text-center mb-6 min-h-[160px]">
                    <h3 class="text-2xl font-bold mb-1">{{ __('home/pricing.enterprise_title') }}</h3>
                    <p class="text-sm text-gray-400 mb-3">{{ __('home/pricing.enterprise_tagline') }}</p>
                    <div class="text-4xl font-bold mb-1">
                        @php
                            $monthlyPrice = $premiumPlan->monthly_price_cents / 100;
                            $currency = $premiumPlan->currency === 'DKK' ? 'kr' : '€';
                        @endphp
                        <span class="text-purple-400">{{ number_format($monthlyPrice, 2, ',', '.') }} {{ $currency }}</span>
                        <span class="text-xl text-gray-400">/{{ __('home/pricing.pro_period') }}</span>
                    </div>
                    @if($premiumPlan->yearly_price_cents)
                        @php
                            $yearlyPrice = $premiumPlan->yearly_price_cents / 100;
                        @endphp
                        <div class="text-gray-400 text-sm">
                            {{ __('home/pricing.enterprise_period_dynamic', [
                                'yearly_price' => number_format($yearlyPrice, 0, ',', '.'),
                                'currency' => $currency
                            ]) }}
                        </div>
                    @else
                        <div class="text-gray-400 text-sm">{{ __('home/pricing.enterprise_period') }}</div>
                    @endif
                </div>
                
                <ul class="space-y-3 mb-8 min-h-[140px]">
                    @foreach(['feat1', 'feat2', 'feat3', 'feat4', 'feat5', 'feat6', 'feat7'] as $feat)
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-purple-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ __('home/pricing.enterprise_' . $feat) }}</span>
                    </li>
                    @endforeach
                </ul>
                
                <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 bg-white/10 hover:bg-white/20 rounded-lg transition font-semibold">
                    {{ __('home/pricing.enterprise_cta') }}
                </a>
            </div>
            @endif
        </div>

        <!-- Bottom CTA -->
        <div class="text-center mt-12">
            <a href="{{ route('pricing') }}" class="inline-flex items-center text-blue-400 hover:text-blue-300 font-semibold text-lg group">
                {{ __('home/pricing.view_all') }}
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
    </div>
</section>
