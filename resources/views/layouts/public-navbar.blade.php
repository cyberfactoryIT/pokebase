<nav class="fixed top-0 w-full z-50 bg-black/95 backdrop-blur-sm border-b border-white/10">
    <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="/" class="flex items-center space-x-2">
                <img src="{{ asset('images/logo_basecard.svg') }}" alt="Basecard" class="h-8">
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition {{ request()->routeIs('about') ? 'text-white font-semibold' : '' }}">{{ __('welcome.nav_about') }}</a>
                <a href="{{ route('features') }}" class="text-gray-300 hover:text-white transition {{ request()->routeIs('features') ? 'text-white font-semibold' : '' }}">{{ __('welcome.nav_features') }}</a>
                <a href="{{ route('pricing') }}" class="text-gray-300 hover:text-white transition {{ request()->routeIs('pricing') ? 'text-white font-semibold' : '' }}">{{ __('welcome.nav_pricing') }}</a>
                <a href="{{ route('contact') }}" class="text-gray-300 hover:text-white transition {{ request()->routeIs('contact') ? 'text-white font-semibold' : '' }}">{{ __('welcome.nav_contact') }}</a>
                
                <!-- Language Selector -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-1 text-gray-300 hover:text-white transition">
                        <span class="uppercase">{{ app()->getLocale() }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-24 bg-[#161615] border border-white/15 rounded-lg shadow-xl py-2">
                        <a href="?lang=da" class="block px-4 py-2 text-sm hover:bg-white/5">Dansk</a>
                        <a href="?lang=en" class="block px-4 py-2 text-sm hover:bg-white/5">English</a>
                        <a href="?lang=it" class="block px-4 py-2 text-sm hover:bg-white/5">Italiano</a>
                    </div>
                </div>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg font-semibold transition">
                        {{ __('welcome.nav_dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition">{{ __('auth.login') }}</a>
                    <a href="{{ route('register') }}" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg font-semibold transition">
                        {{ __('auth.register') }}
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" class="md:hidden mt-4 pb-4 space-y-4">
            <a href="{{ route('about') }}" class="block text-gray-300 hover:text-white transition">{{ __('welcome.nav_about') }}</a>
            <a href="{{ route('features') }}" class="block text-gray-300 hover:text-white transition">{{ __('welcome.nav_features') }}</a>
            <a href="{{ route('pricing') }}" class="block text-gray-300 hover:text-white transition">{{ __('welcome.nav_pricing') }}</a>
            <a href="{{ route('contact') }}" class="block text-gray-300 hover:text-white transition">{{ __('welcome.nav_contact') }}</a>
            @auth
                <a href="{{ route('dashboard') }}" class="block px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg font-semibold text-center">
                    {{ __('welcome.nav_dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="block text-gray-300 hover:text-white transition">{{ __('auth.login') }}</a>
                <a href="{{ route('register') }}" class="block px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg font-semibold text-center">
                    {{ __('auth.register') }}
                </a>
            @endauth
        </div>
    </div>
</nav>
