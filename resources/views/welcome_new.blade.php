<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} - {{ __('welcome.subtitle_short') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
            }

            /* Gradient text effect */
            .gradient-text {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            /* Smooth animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in-up {
                animation: fadeInUp 0.8s ease-out forwards;
            }

            /* Staggered animation delays */
            .delay-100 { animation-delay: 0.1s; opacity: 0; }
            .delay-200 { animation-delay: 0.2s; opacity: 0; }
            .delay-300 { animation-delay: 0.3s; opacity: 0; }
            .delay-400 { animation-delay: 0.4s; opacity: 0; }
        </style>
    </head>
    <body class="bg-[#1a1a1a] text-white antialiased">
        
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-[#1a1a1a]/95 backdrop-blur-sm border-b border-white/10">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <img src="/images/logo_basecard.svg" alt="{{ config('app.name') }}" class="h-8 w-auto">
                        <span class="text-xl font-bold text-white">{{ config('app.name') }}</span>
                    </div>

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

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 px-6 lg:px-8 overflow-hidden">
            <!-- Background gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/20 via-purple-900/20 to-pink-900/20"></div>
            
            <div class="max-w-7xl mx-auto relative z-10">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left: Text Content -->
                    <div class="space-y-8">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight animate-fade-in-up">
                            {{ __('welcome.hero_title') }}
                        </h1>
                        
                        <p class="text-xl text-gray-300 leading-relaxed animate-fade-in-up delay-100">
                            {{ __('welcome.hero_subtitle') }}
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 animate-fade-in-up delay-200">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-xl text-lg font-semibold transition shadow-lg shadow-blue-500/50">
                                {{ __('welcome.cta_create_profile') }}
                            </a>
                            <a href="#features" class="inline-flex items-center justify-center px-8 py-4 bg-white/10 hover:bg-white/20 rounded-xl text-lg font-semibold transition backdrop-blur-sm">
                                {{ __('welcome.cta_learn_more') }}
                            </a>
                        </div>

                        <!-- Game Stats -->
                        <div class="flex items-center gap-8 pt-8 animate-fade-in-up delay-300">
                            <div>
                                <div class="text-3xl font-bold gradient-text">{{ __('welcome.stat_games') }}</div>
                                <div class="text-sm text-gray-400">{{ __('welcome.stat_games_label') }}</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold gradient-text">{{ __('welcome.stat_cardmarket') }}</div>
                                <div class="text-sm text-gray-400">{{ __('welcome.stat_cardmarket_label') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Phone Mockup -->
                    <div class="relative animate-fade-in-up delay-400">
                        <div class="relative mx-auto max-w-sm">
                            <!-- Phone frame -->
                            <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-[3rem] p-3 shadow-2xl">
                                <div class="bg-black rounded-[2.5rem] overflow-hidden">
                                    <!-- Status bar -->
                                    <div class="h-10 bg-black flex items-center justify-between px-6 text-xs">
                                        <span>9:41</span>
                                        <div class="flex gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Content placeholder -->
                                    <div class="aspect-[9/16] bg-gradient-to-br from-blue-600/20 to-purple-600/20 flex items-center justify-center">
                                        <div class="text-center p-8">
                                            <div class="text-6xl font-bold mb-4">{{ __('welcome.mockup_value') }}</div>
                                            <div class="text-sm text-gray-400">{{ __('welcome.mockup_label') }}</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Bottom bar -->
                                    <div class="h-8 bg-black"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- The Three Pillars Section -->
        <section id="features" class="py-20 px-6 lg:px-8 bg-black/30">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-16">
                    {{ __('welcome.pillars_title') }}
                </h2>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Pillar 1 -->
                    <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl p-8 hover:border-white/20 transition group">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500/20 to-green-600/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">{{ __('welcome.pillar1_title') }}</h3>
                        <p class="text-gray-400 leading-relaxed">{{ __('welcome.pillar1_description') }}</p>
                    </div>

                    <!-- Pillar 2 -->
                    <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl p-8 hover:border-white/20 transition group">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">{{ __('welcome.pillar2_title') }}</h3>
                        <p class="text-gray-400 leading-relaxed">{{ __('welcome.pillar2_description') }}</p>
                    </div>

                    <!-- Pillar 3 -->
                    <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl p-8 hover:border-white/20 transition group">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500/20 to-purple-600/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">{{ __('welcome.pillar3_title') }}</h3>
                        <p class="text-gray-400 leading-relaxed">{{ __('welcome.pillar3_description') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Social Proof - TEMPORANEAMENTE NASCOSTO (riattivare dopo lancio) -->
        {{-- 
        <section class="py-20 px-6 lg:px-8">
            <div class="max-w-7xl mx-auto text-center">
                <div class="flex items-center justify-center gap-4 mb-4">
                    <!-- Avatar circles -->
                    <div class="flex -space-x-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 border-2 border-[#1a1a1a]"></div>
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 border-2 border-[#1a1a1a]"></div>
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-400 to-pink-600 border-2 border-[#1a1a1a]"></div>
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 border-2 border-[#1a1a1a]"></div>
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 border-2 border-[#1a1a1a]"></div>
                    </div>
                </div>
                <p class="text-xl text-gray-300">
                    {{ __('welcome.social_proof') }}
                </p>
            </div>
        </section>
        --}}

        <!-- Footer -->
        <footer class="border-t border-white/10 py-12 px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo_basecard.svg" alt="{{ config('app.name') }}" class="h-8 w-auto">
                        <span class="text-xl font-bold">{{ config('app.name') }}</span>
                    </div>
                    
                    <div class="flex gap-8 text-sm text-gray-400">
                        <a href="{{ route('privacy') }}" class="hover:text-white transition">{{ __('footer.privacy_policy') }}</a>
                        <a href="{{ route('terms') }}" class="hover:text-white transition">{{ __('footer.terms_of_service') }}</a>
                        <a href="{{ route('contact') }}" class="hover:text-white transition">{{ __('footer.contact') }}</a>
                    </div>
                </div>
                
                <div class="mt-8 text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('footer.all_rights_reserved') }}
                </div>
            </div>
        </footer>

        <script>
            function toggleMobileMenu() {
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            }
        </script>
    </body>
</html>
