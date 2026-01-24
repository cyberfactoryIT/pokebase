<nav class="fixed top-0 left-0 right-0 z-50 bg-[#1a1a1a]/95 backdrop-blur-sm border-b border-white/10">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 hover:opacity-80 transition">
                <img src="/images/logo_basecard.svg" alt="{{ config('app.name') }}" class="h-8 w-auto">
                <span class="text-xl font-bold text-white">{{ config('app.name') }}</span>
            </a>

            <!-- Menu Desktop -->
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition">{{ __('welcome.nav_about') }}</a>
                <a href="{{ route('features') }}" class="text-gray-300 hover:text-white transition">{{ __('welcome.nav_features') }}</a>
                <a href="{{ route('pricing') }}" class="text-gray-300 hover:text-white transition">{{ __('welcome.nav_pricing') }}</a>
                <a href="{{ route('contact') }}" class="text-gray-300 hover:text-white transition">{{ __('welcome.nav_contact') }}</a>
                
                <!-- Language Selector -->
                <form method="POST" action="{{ route('language.change') }}" class="ml-4">
                    @csrf
                    <select name="locale" onchange="this.form.submit()" class="bg-transparent border border-white/20 text-white rounded px-3 py-1.5 text-sm focus:outline-none focus:border-white/40">
                        <option value="en" @if(app()->getLocale() == 'en') selected @endif>EN</option>
                        <option value="it" @if(app()->getLocale() == 'it') selected @endif>IT</option>
                        <option value="da" @if(app()->getLocale() == 'da') selected @endif>DA</option>
                    </select>
                </form>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition">
                        {{ __('welcome.nav_dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition">
                        {{ __('auth.login') }}
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg transition font-medium">
                        {{ __('welcome.cta_start_free') }}
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <button class="md:hidden text-white" onclick="toggleMobileMenu()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-[#1a1a1a] border-t border-white/10">
        <div class="px-6 py-4 space-y-3">
            <a href="{{ route('about') }}" class="block text-gray-300 hover:text-white transition">{{ __('welcome.nav_about') }}</a>
            <a href="{{ route('features') }}" class="block text-gray-300 hover:text-white transition">{{ __('welcome.nav_features') }}</a>
            <a href="{{ route('pricing') }}" class="block text-gray-300 hover:text-white transition">{{ __('welcome.nav_pricing') }}</a>
            <a href="{{ route('contact') }}" class="block text-gray-300 hover:text-white transition">{{ __('welcome.nav_contact') }}</a>
            @guest
                <a href="{{ route('login') }}" class="block text-gray-300 hover:text-white transition">{{ __('auth.login') }}</a>
                <a href="{{ route('register') }}" class="block px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg text-center">
                    {{ __('welcome.cta_start_free') }}
                </a>
            @endguest
        </div>
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }
</script>
