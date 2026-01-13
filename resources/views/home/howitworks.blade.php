<!-- How It Works Section -->
<section class="py-24 px-6 lg:px-8 bg-black">
    <div class="max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-20">
            <div class="inline-block px-4 py-2 bg-blue-600/20 rounded-full text-blue-400 text-sm font-semibold mb-4">
                {{ __('home/howitworks.badge') }}
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                {{ __('home/howitworks.title') }}
            </h2>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                {{ __('home/howitworks.subtitle') }}
            </p>
        </div>

        <!-- Steps -->
        <div class="relative">
            <!-- Connecting Line (hidden on mobile) -->
            <div class="hidden md:block absolute top-20 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 opacity-20"></div>
            
            <div class="grid md:grid-cols-4 gap-8">
                @foreach(['step1', 'step2', 'step3', 'step4'] as $index => $step)
                <div class="relative">
                    <!-- Step Number -->
                    <div class="relative z-10 w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
                        {{ $index + 1 }}
                    </div>
                    
                    <!-- Content -->
                    <div class="text-center">
                        <!-- Icon -->
                        <div class="w-20 h-20 mx-auto mb-6 bg-white/5 rounded-2xl flex items-center justify-center">
                            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($step === 'step1')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                @elseif($step === 'step2')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                @elseif($step === 'step3')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path>
                                @endif
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold mb-3">
                            {{ __('home/howitworks.' . $step . '_title') }}
                        </h3>
                        <p class="text-gray-400 leading-relaxed">
                            {{ __('home/howitworks.' . $step . '_description') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-16">
            <a href="{{ route('register') }}" class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-xl text-lg font-semibold transition shadow-lg shadow-blue-500/50">
                {{ __('home/howitworks.cta') }}
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
            <p class="text-gray-400 mt-4">{{ __('home/howitworks.cta_subtitle') }}</p>
        </div>
    </div>
</section>
