<!-- HERO SECTION -->
<div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 py-32">
    <!-- Animated background -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-10 left-10 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
        <div class="absolute top-0 right-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
    </div>
    
    <div class="relative container mx-auto px-6 text-center max-w-5xl">
        <h1 class="text-5xl md:text-7xl font-bold mb-8 leading-tight">
            {{ __('features/hero.title') }}
        </h1>
        <p class="text-xl md:text-2xl text-blue-100 mb-12 max-w-3xl mx-auto leading-relaxed">
            {{ __('features/hero.subtitle') }}
        </p>
        
        <!-- Features badges -->
        <div class="flex flex-wrap justify-center gap-4 mb-12">
            <span class="px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full text-base font-semibold">
                {{ __('features/hero.badge1') }}
            </span>
            <span class="px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full text-base font-semibold">
                {{ __('features/hero.badge2') }}
            </span>
            <span class="px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full text-base font-semibold">
                {{ __('features/hero.badge3') }}
            </span>
        </div>
        
        <!-- CTAs -->
        <div class="flex flex-col sm:flex-row gap-6 justify-center">
            <a href="{{ route('register') }}" class="px-10 py-5 bg-white text-purple-600 rounded-xl font-bold text-lg hover:bg-gray-100 hover:scale-105 transition-all shadow-2xl">
                {{ __('features/hero.cta1') }}
            </a>
            <a href="#demo" class="px-10 py-5 bg-white/10 backdrop-blur-sm border-2 border-white/30 rounded-xl font-semibold text-lg hover:bg-white/20 hover:border-white/50 transition-all">
                {{ __('features/hero.cta2') }}
            </a>
        </div>
    </div>
</div>
