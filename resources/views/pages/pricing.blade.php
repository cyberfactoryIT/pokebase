@extends('layouts.public')

@section('title', __('pages.pricing_title') . ' - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-black text-white py-20">
    <div class="container mx-auto px-6">
        <!-- Header -->
        <div class="text-center mb-16">
            <p class="text-lime-400 uppercase tracking-wider text-sm font-semibold mb-4">{{ __('pages.pricing_header') }}</p>
            <h1 class="text-5xl font-bold mb-4">{{ __('pages.pricing_title') }}</h1>
            <p class="text-xl text-gray-400">{{ __('pages.pricing_subtitle') }}</p>
        </div>

        <!-- Pricing Cards -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
            @forelse($plans as $index => $plan)
            @php
                // Determine if this plan should be highlighted (typically the middle/recommended one)
                $isRecommended = $plan->meta['recommended'] ?? false;
                $isHighlight = $isRecommended;
                
                // Get plan description from meta or translation
                $planKey = strtolower($plan->code);
                $planDesc = $plan->meta['description'] ?? __('pages.pricing_' . $planKey . '_desc');
                
                // Format price
                $priceMonthly = $plan->monthly_price_cents / 100;
                
                // Get CTA text
                if ($priceMonthly == 0) {
                    $ctaText = __('pages.pricing_basic_cta');
                } elseif ($isRecommended) {
                    $ctaText = __('pages.pricing_ultra_cta');
                } else {
                    $ctaText = __('pages.pricing_upgrade_cta');
                }
            @endphp
            
            <div class="relative bg-[#161615] border {{ $isHighlight ? 'border-lime-400/50' : 'border-white/15' }} rounded-lg p-8 hover:border-blue-500/50 transition-all {{ $isHighlight ? 'transform scale-105' : '' }}">
                <!-- Recommended Badge -->
                @if($isRecommended)
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-lime-400 text-black text-xs font-bold px-4 py-1 rounded-full uppercase">
                        {{ __('pages.pricing_recommended') }}
                    </span>
                </div>
                @endif

                <!-- Plan Name -->
                <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                
                <!-- Price -->
                <div class="mb-4">
                    <span class="text-5xl font-bold">{{ number_format($priceMonthly, 0) }} kr.</span>
                    <span class="text-gray-400">/{{ __('pages.pricing_period') }}</span>
                </div>

                <!-- Description -->
                <p class="text-gray-400 mb-6">{{ $planDesc }}</p>

                <!-- Features -->
                <ul class="space-y-3 mb-8">
                    @foreach($plan->features as $feature)
                    @php
                        $featureValue = $feature->pivot->value;
                        $featureName = $feature->name;
                        
                        // Format feature text based on value
                        if (is_numeric($featureValue)) {
                            $displayText = number_format($featureValue) . ' ' . $featureName;
                        } elseif ($featureValue === 'true' || $featureValue === true) {
                            $displayText = $featureName;
                        } else {
                            $displayText = $featureValue . ' ' . $featureName;
                        }
                    @endphp
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-lime-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ $displayText }}</span>
                    </li>
                    @endforeach
                </ul>

                <!-- CTA Button -->
                <a href="{{ route('register') }}" class="block w-full text-center py-3 rounded-lg font-semibold transition-all {{ $isHighlight ? 'bg-lime-400 text-black hover:bg-lime-500' : 'bg-white/10 text-white hover:bg-white/20' }}">
                    {{ $ctaText }}
                </a>
            </div>
            @empty
            <!-- Fallback if no plans in database -->
            @php
                $plans = [
                    [
                        'name' => __('pages.pricing_basic_name'),
                        'price' => '0',
                        'period' => __('pages.pricing_period'),
                        'description' => __('pages.pricing_basic_desc'),
                        'features' => [
                            __('pages.pricing_basic_feat1'),
                            __('pages.pricing_basic_feat2'),
                            __('pages.pricing_basic_feat3'),
                            __('pages.pricing_basic_feat4'),
                        ],
                        'cta' => __('pages.pricing_basic_cta'),
                        'cta_url' => route('register'),
                        'recommended' => false,
                        'highlight' => false,
                    ],
                    [
                        'name' => __('pages.pricing_pro_name'),
                        'price' => '9.90',
                        'period' => __('pages.pricing_period'),
                        'description' => __('pages.pricing_pro_desc'),
                        'features' => [
                            __('pages.pricing_pro_feat1'),
                            __('pages.pricing_pro_feat2'),
                            __('pages.pricing_pro_feat3'),
                            __('pages.pricing_pro_feat4'),
                        ],
                        'cta' => __('pages.pricing_upgrade_cta'),
                        'cta_url' => route('register'),
                        'recommended' => false,
                        'highlight' => false,
                    ],
                    [
                        'name' => __('pages.pricing_ultra_name'),
                        'price' => '24.90',
                        'period' => __('pages.pricing_period'),
                        'description' => __('pages.pricing_ultra_desc'),
                        'features' => [
                            __('pages.pricing_ultra_feat1'),
                            __('pages.pricing_ultra_feat2'),
                            __('pages.pricing_ultra_feat3'),
                            __('pages.pricing_ultra_feat4'),
                        ],
                        'cta' => __('pages.pricing_ultra_cta'),
                        'cta_url' => route('register'),
                        'recommended' => true,
                        'highlight' => true,
                    ],
                    [
                        'name' => __('pages.pricing_mega_name'),
                        'price' => '49.50',
                        'period' => __('pages.pricing_period'),
                        'description' => __('pages.pricing_mega_desc'),
                        'features' => [
                            __('pages.pricing_mega_feat1'),
                            __('pages.pricing_mega_feat2'),
                            __('pages.pricing_mega_feat3'),
                            __('pages.pricing_mega_feat4'),
                        ],
                        'cta' => __('pages.pricing_upgrade_cta'),
                        'cta_url' => route('register'),
                        'recommended' => false,
                        'highlight' => false,
                    ],
                ];
            @endphp

            @foreach($plans as $plan)
            <div class="relative bg-[#161615] border {{ $plan['highlight'] ? 'border-lime-400/50' : 'border-white/15' }} rounded-lg p-8 hover:border-blue-500/50 transition-all {{ $plan['highlight'] ? 'transform scale-105' : '' }}">
                @if($plan['recommended'])
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-lime-400 text-black text-xs font-bold px-4 py-1 rounded-full uppercase">
                        {{ __('pages.pricing_recommended') }}
                    </span>
                </div>
                @endif

                <h3 class="text-2xl font-bold mb-2">{{ $plan['name'] }}</h3>
                
                <div class="mb-4">
                    <span class="text-5xl font-bold">{{ $plan['price'] }} kr.</span>
                    <span class="text-gray-400">/{{ $plan['period'] }}</span>
                </div>

                <p class="text-gray-400 mb-6">{{ $plan['description'] }}</p>

                <ul class="space-y-3 mb-8">
                    @foreach($plan['features'] as $feature)
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-lime-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>

                <a href="{{ $plan['cta_url'] }}" class="block w-full text-center py-3 rounded-lg font-semibold transition-all {{ $plan['highlight'] ? 'bg-lime-400 text-black hover:bg-lime-500' : 'bg-white/10 text-white hover:bg-white/20' }}">
                    {{ $plan['cta'] }}
                </a>
            </div>
            @endforeach
            @endforelse
        </div>

        <!-- Enterprise Section -->
        <div class="text-center">
            <p class="text-gray-400 text-lg">
                {{ __('pages.pricing_enterprise_text') }} 
                <a href="{{ route('contact') }}" class="text-lime-400 hover:text-lime-300 font-semibold">{{ __('pages.pricing_enterprise_link') }}</a>
                {{ __('pages.pricing_enterprise_text2') }}
            </p>
        </div>

        <!-- FAQ Section -->
        <div class="mt-24 max-w-3xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">{{ __('pages.pricing_faq_title') }}</h2>
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

                @foreach($faqs as $faq)
                <details class="bg-[#161615] border border-white/15 rounded-lg p-6 hover:border-blue-500/50 transition-colors group">
                    <summary class="font-semibold text-lg cursor-pointer list-none flex items-center justify-between">
                        <span>{{ $faq['question'] }}</span>
                        <svg class="w-5 h-5 text-gray-400 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    <div class="mt-4 text-gray-400">
                        {{ $faq['answer'] }}
                    </div>
                </details>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
