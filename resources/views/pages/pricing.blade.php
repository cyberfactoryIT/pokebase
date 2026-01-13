@extends('layouts.public')

@section('title', __('pages.pricing_title') . ' - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-[#1a1a1a] text-white py-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-16">
            <div class="inline-block px-4 py-2 bg-blue-600/20 rounded-full text-blue-400 text-sm font-semibold mb-4">
                {{ __('pages.pricing_header') }}
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">{{ __('pages.pricing_title') }}</h1>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">{{ __('pages.pricing_subtitle') }}</p>
            
            <!-- Billing Toggle -->
            <div class="flex items-center justify-center gap-4 mt-8">
                <span class="text-gray-400" id="monthly-label">{{ __('pages.pricing_monthly') }}</span>
                <button onclick="toggleBilling()" class="relative inline-flex h-8 w-14 items-center rounded-full bg-white/10 transition-colors focus:outline-none" id="billing-toggle">
                    <span class="inline-block h-6 w-6 transform rounded-full bg-blue-500 transition-transform translate-x-1" id="toggle-dot"></span>
                </button>
                <span class="text-gray-400" id="yearly-label">{{ __('pages.pricing_yearly') }}</span>
                <span class="ml-2 px-3 py-1 bg-green-600/20 text-green-400 text-sm font-semibold rounded-full">{{ __('pages.pricing_save_17') }}</span>
            </div>
        </div>

        </div>

        <!-- Pricing Cards -->
        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-24">
            @php
                $pricingPlans = \App\Models\PricingPlan::orderBy('monthly_price_cents')->get();
            @endphp

            @foreach($pricingPlans as $plan)
            @php
                $isRecommended = $plan->code === 'advanced'; // Advanced is recommended
                $monthlyPrice = $plan->monthly_price_cents / 100;
                $yearlyPrice = $plan->yearly_price_cents / 100;
                $currency = $plan->currency === 'DKK' ? 'kr' : '€';
                $savings = $yearlyPrice > 0 ? round((1 - ($yearlyPrice / 12) / $monthlyPrice) * 100) : 0;
                
                // Color scheme per plan
                $colors = [
                    'free' => ['accent' => 'green', 'from' => 'from-green-600', 'to' => 'to-green-700'],
                    'advanced' => ['accent' => 'blue', 'from' => 'from-blue-600', 'to' => 'to-purple-600'],
                    'premium' => ['accent' => 'purple', 'from' => 'from-purple-600', 'to' => 'to-pink-600'],
                ];
                $color = $colors[$plan->code] ?? $colors['advanced'];
            @endphp

            <div class="relative bg-[#1a1a1a] border {{ $isRecommended ? 'border-2 border-blue-500' : 'border border-white/10' }} rounded-2xl p-8 hover:border-{{ $color['accent'] }}-500/50 transition-all {{ $isRecommended ? 'transform md:scale-105 shadow-2xl shadow-blue-500/20' : '' }}">
                
                <!-- Recommended Badge -->
                @if($isRecommended)
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-gradient-to-r {{ $color['from'] }} {{ $color['to'] }} rounded-full text-sm font-bold shadow-lg">
                    {{ __('pages.pricing_recommended') }}
                </div>
                @endif

                <!-- Plan Header -->
                <div class="text-center mb-8 {{ $isRecommended ? 'mt-2' : '' }}">
                    <h3 class="text-2xl font-bold mb-4">{{ $plan->name }}</h3>
                    
                    <!-- Monthly Price -->
                    <div class="price-monthly">
                        <div class="text-5xl font-bold mb-2">
                            <span class="text-{{ $color['accent'] }}-400">
                                @if($monthlyPrice == 0)
                                    0
                                @else
                                    {{ number_format($monthlyPrice, 2, ',', '.') }}
                                @endif
                            </span>
                            <span class="text-2xl text-gray-400"> {{ $currency }}</span>
                        </div>
                        <div class="text-gray-400 text-sm">{{ __('pages.pricing_per_month') }}</div>
                    </div>

                    <!-- Yearly Price (Hidden by default) -->
                    @if($yearlyPrice > 0)
                    <div class="price-yearly hidden">
                        <div class="text-5xl font-bold mb-2">
                            <span class="text-{{ $color['accent'] }}-400">{{ number_format($yearlyPrice, 0, ',', '.') }}</span>
                            <span class="text-2xl text-gray-400"> {{ $currency }}</span>
                        </div>
                        <div class="text-gray-400 text-sm">
                            {{ __('pages.pricing_per_year') }}
                            <span class="text-green-400 font-semibold ml-2">({{ __('pages.pricing_save') }} {{ $savings }}%)</span>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Features -->
                <ul class="space-y-4 mb-8 min-h-[280px]">
                    @forelse($plan->features as $feature)
                    @php
                        $featureValue = $feature->pivot->value;
                        $featureName = $feature->name;
                        
                        // Format feature text
                        if (is_numeric($featureValue)) {
                            $displayText = number_format($featureValue) . ' ' . $featureName;
                        } elseif ($featureValue === 'true' || $featureValue === true || $featureValue === '1') {
                            $displayText = $featureName;
                        } elseif ($featureValue === 'unlimited') {
                            $displayText = __('pages.pricing_unlimited') . ' ' . $featureName;
                        } else {
                            $displayText = $featureValue . ' ' . $featureName;
                        }
                    @endphp
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ $displayText }}</span>
                    </li>
                    @empty
                    <!-- Fallback features based on plan code -->
                    @php
                        $fallbackFeatures = [
                            'free' => [
                                __('pages.pricing_basic_feat1'),
                                __('pages.pricing_basic_feat2'),
                                __('pages.pricing_basic_feat3'),
                                __('pages.pricing_basic_feat4'),
                            ],
                            'advanced' => [
                                __('pages.pricing_pro_feat1'),
                                __('pages.pricing_pro_feat2'),
                                __('pages.pricing_pro_feat3'),
                                __('pages.pricing_pro_feat4'),
                                __('pages.pricing_pro_feat5'),
                            ],
                            'premium' => [
                                __('pages.pricing_ultra_feat1'),
                                __('pages.pricing_ultra_feat2'),
                                __('pages.pricing_ultra_feat3'),
                                __('pages.pricing_ultra_feat4'),
                                __('pages.pricing_ultra_feat5'),
                            ],
                        ];
                        $features = $fallbackFeatures[$plan->code] ?? [];
                    @endphp
                    @foreach($features as $feat)
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-{{ $color['accent'] }}-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ $feat }}</span>
                    </li>
                    @endforeach
                    @endforelse
                </ul>

                <!-- CTA Button -->
                @if($monthlyPrice == 0)
                    <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 bg-white/10 hover:bg-white/20 rounded-xl transition font-semibold text-lg">
                        {{ __('pages.pricing_basic_cta') }}
                    </a>
                @else
                    <a href="{{ route('register') }}" class="block w-full text-center px-6 py-4 {{ $isRecommended ? 'bg-gradient-to-r ' . $color['from'] . ' ' . $color['to'] . ' hover:opacity-90 shadow-lg' : 'bg-white/10 hover:bg-white/20' }} rounded-xl transition font-semibold text-lg">
                        {{ $isRecommended ? __('pages.pricing_ultra_cta') : __('pages.pricing_upgrade_cta') }}
                    </a>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Features Comparison Section -->
        <div class="text-center bg-gradient-to-br from-blue-900/10 to-purple-900/10 rounded-2xl p-12 mb-24">
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

        <!-- FAQ Section -->
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">{{ __('pages.pricing_faq_title') }}</h2>
                <p class="text-gray-400">{{ __('pages.pricing_faq_subtitle') }}</p>
            </div>

            <div class="space-y-4">
                @php
                    $faqs = [
                        [
                            'question' => __('pages.pricing_faq1_q'),
                            'answer' => __('pages.pricing_faq1_a'),
                        ],
                        [
                            'question' => __('pages.pricing_faq2_q'),
                            'answer' => __('pages.pricing_faq2_a'),
                        ],
                        [
                            'question' => __('pages.pricing_faq3_q'),
                            'answer' => __('pages.pricing_faq3_a'),
                        ],
                        [
                            'question' => __('pages.pricing_faq4_q'),
                            'answer' => __('pages.pricing_faq4_a'),
                        ],
                    ];
                @endphp

                @foreach($faqs as $index => $faq)
                <details class="bg-[#1a1a1a] border border-white/10 rounded-xl p-6 hover:border-blue-500/50 transition-all group">
                    <summary class="font-semibold text-lg cursor-pointer list-none flex items-center justify-between">
                        <span class="flex items-center">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600/20 text-blue-400 font-bold text-sm mr-4">
                                {{ $index + 1 }}
                            </span>
                            {{ $faq['question'] }}
                        </span>
                        <svg class="w-6 h-6 text-gray-400 transform group-open:rotate-180 transition-transform flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="mt-4 pl-12 text-gray-400 leading-relaxed">
                        {{ $faq['answer'] }}
                    </div>
                </details>
                @endforeach
            </div>

            <!-- Contact Support -->
            <div class="text-center mt-12 p-8 bg-[#1a1a1a] border border-white/10 rounded-xl">
                <p class="text-gray-400 mb-4">{{ __('pages.pricing_more_questions') }}</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center text-blue-400 hover:text-blue-300 font-semibold">
                    {{ __('pages.pricing_contact_support') }}
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
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
