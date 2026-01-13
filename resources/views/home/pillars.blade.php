<!-- The Three Pillars Section -->
<section id="features" class="py-24 px-6 lg:px-8 bg-black/30">
    <div class="max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-20">
            <div class="inline-block px-4 py-2 bg-purple-600/20 rounded-full text-purple-400 text-sm font-semibold mb-4">
                {{ __('home/pillars.badge') }}
            </div>
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                {{ __('home/pillars.title') }}
            </h2>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                {{ __('home/pillars.subtitle') }}
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <!-- Pillar 1: Track -->
            <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161615] border border-white/10 rounded-2xl p-8 hover:border-green-500/50 hover:shadow-lg hover:shadow-green-500/20 transition-all duration-300 group">
                <!-- Icon -->
                <div class="w-20 h-20 bg-gradient-to-br from-green-500/20 to-emerald-600/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                
                <!-- Content -->
                <h3 class="text-2xl font-bold mb-4 text-green-400">
                    {{ __('home/pillars.pillar1_title') }}
                </h3>
                <p class="text-gray-400 mb-6 leading-relaxed">
                    {{ __('home/pillars.pillar1_description') }}
                </p>
                
                <!-- Features list -->
                <ul class="space-y-3">
                    @foreach(['feat1', 'feat2', 'feat3', 'feat4'] as $feat)
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ __('home/pillars.pillar1_' . $feat) }}</span>
                    </li>
                    @endforeach
                </ul>
                
                <!-- CTA -->
                <div class="mt-8 pt-6 border-t border-white/10">
                    <a href="{{ route('features') }}#collection" class="text-green-400 hover:text-green-300 font-semibold inline-flex items-center group">
                        {{ __('home/pillars.learn_more') }}
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Pillar 2: Value -->
            <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161615] border border-white/10 rounded-2xl p-8 hover:border-blue-500/50 hover:shadow-lg hover:shadow-blue-500/20 transition-all duration-300 group">
                <!-- Icon -->
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500/20 to-purple-600/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                
                <!-- Content -->
                <h3 class="text-2xl font-bold mb-4 text-blue-400">
                    {{ __('home/pillars.pillar2_title') }}
                </h3>
                <p class="text-gray-400 mb-6 leading-relaxed">
                    {{ __('home/pillars.pillar2_description') }}
                </p>
                
                <!-- Features list -->
                <ul class="space-y-3">
                    @foreach(['feat1', 'feat2', 'feat3', 'feat4'] as $feat)
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ __('home/pillars.pillar2_' . $feat) }}</span>
                    </li>
                    @endforeach
                </ul>
                
                <!-- CTA -->
                <div class="mt-8 pt-6 border-t border-white/10">
                    <a href="{{ route('features') }}#market" class="text-blue-400 hover:text-blue-300 font-semibold inline-flex items-center group">
                        {{ __('home/pillars.learn_more') }}
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Pillar 3: Play -->
            <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161615] border border-white/10 rounded-2xl p-8 hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-500/20 transition-all duration-300 group">
                <!-- Icon -->
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500/20 to-pink-600/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                
                <!-- Content -->
                <h3 class="text-2xl font-bold mb-4 text-purple-400">
                    {{ __('home/pillars.pillar3_title') }}
                </h3>
                <p class="text-gray-400 mb-6 leading-relaxed">
                    {{ __('home/pillars.pillar3_description') }}
                </p>
                
                <!-- Features list -->
                <ul class="space-y-3">
                    @foreach(['feat1', 'feat2', 'feat3', 'feat4'] as $feat)
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-purple-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-300">{{ __('home/pillars.pillar3_' . $feat) }}</span>
                    </li>
                    @endforeach
                </ul>
                
                <!-- CTA -->
                <div class="mt-8 pt-6 border-t border-white/10">
                    <a href="{{ route('features') }}#deckbuilder" class="text-purple-400 hover:text-purple-300 font-semibold inline-flex items-center group">
                        {{ __('home/pillars.learn_more') }}
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom CTA -->
        <div class="text-center mt-16">
            <a href="{{ route('features') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-xl text-lg font-semibold transition shadow-lg">
                {{ __('home/pillars.explore_all') }}
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
    </div>
</section>
