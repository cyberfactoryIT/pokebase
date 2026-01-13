<!-- Social Proof Section -->
<section class="py-24 px-6 lg:px-8 bg-gradient-to-br from-purple-900/20 to-blue-900/20">
    <div class="max-w-7xl mx-auto">
        <!-- Stats Grid -->
        <div class="grid md:grid-cols-4 gap-8 mb-20">
            <div class="text-center">
                <div class="text-5xl md:text-6xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent mb-2">
                    {{ __('home/social.stat1_number') }}
                </div>
                <div class="text-gray-400 text-lg">{{ __('home/social.stat1_label') }}</div>
            </div>
            <div class="text-center">
                <div class="text-5xl md:text-6xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent mb-2">
                    {{ __('home/social.stat2_number') }}
                </div>
                <div class="text-gray-400 text-lg">{{ __('home/social.stat2_label') }}</div>
            </div>
            <div class="text-center">
                <div class="text-5xl md:text-6xl font-bold bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent mb-2">
                    {{ __('home/social.stat3_number') }}
                </div>
                <div class="text-gray-400 text-lg">{{ __('home/social.stat3_label') }}</div>
            </div>
            <div class="text-center">
                <div class="text-5xl md:text-6xl font-bold bg-gradient-to-r from-pink-400 to-red-400 bg-clip-text text-transparent mb-2">
                    {{ __('home/social.stat4_number') }}
                </div>
                <div class="text-gray-400 text-lg">{{ __('home/social.stat4_label') }}</div>
            </div>
        </div>

        <!-- Testimonials -->
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                {{ __('home/social.testimonials_title') }}
            </h2>
            <p class="text-xl text-gray-400">
                {{ __('home/social.testimonials_subtitle') }}
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach(['test1', 'test2', 'test3'] as $test)
            <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl p-8 hover:border-white/20 transition">
                <!-- Stars -->
                <div class="flex gap-1 mb-4">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    @endfor
                </div>
                
                <!-- Quote -->
                <p class="text-gray-300 mb-6 leading-relaxed">
                    "{{ __('home/social.' . $test . '_quote') }}"
                </p>
                
                <!-- Author -->
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500/20 to-blue-500/20 rounded-full flex items-center justify-center mr-4">
                        <span class="text-xl font-bold">{{ __('home/social.' . $test . '_initials') }}</span>
                    </div>
                    <div>
                        <div class="font-semibold">{{ __('home/social.' . $test . '_name') }}</div>
                        <div class="text-sm text-gray-400">{{ __('home/social.' . $test . '_role') }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Trust Badges -->
        <div class="mt-16 pt-12 border-t border-white/10">
            <div class="flex flex-wrap justify-center items-center gap-12 opacity-50">
                <div class="text-2xl font-bold">🇪🇺 GDPR</div>
                <div class="text-2xl font-bold">🔒 SSL</div>
                <div class="text-2xl font-bold">⚡ 99.9% Uptime</div>
                <div class="text-2xl font-bold">🛡️ Secure</div>
            </div>
        </div>
    </div>
</section>
