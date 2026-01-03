<nav x-data="{ open: false }" class="bg-[#161615] border-b border-white/15 shadow-xl">
    @auth
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <img src="/images/logo_basecard.jpg" alt="Logo" class="h-8 w-auto">
                    <span class="font-bold text-lg text-white">Basecard</span>
                </a>
            </div>
            
            <!-- Horizontal Menu -->
            <div class="hidden md:flex items-center gap-6">
                <!-- Current Game Dropdown -->
                @if(! auth()->user()->hasRole('superadmin'))
                <div class="relative" x-data="{ gameOpen: false }">
                    <button @click="gameOpen = !gameOpen" class="flex items-center gap-2 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                        @if($currentGame)
                            @if($currentGame->code === 'pokemon')
                                <img src="/images/logos/logo_pokemon.png" alt="{{ $currentGame->name }}" class="w-5 h-5 object-contain">
                            @elseif($currentGame->code === 'mtg')
                                <span class="text-sm font-bold">MTG</span>
                            @elseif($currentGame->code === 'yugioh')
                                <span class="text-sm font-bold">YGO</span>
                            @endif
                            <span class="text-sm">{{ $currentGame->name }}</span>
                        @else
                            <span class="text-sm">Select Game</span>
                        @endif
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': gameOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div x-show="gameOpen" @click.away="gameOpen = false" x-cloak class="absolute left-0 mt-2 w-64 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl z-50">
                        @if($availableGames->isEmpty())
                            <div class="px-4 py-6 text-center">
                                <p class="text-gray-400 text-sm mb-3">No games activated</p>
                                <a href="{{ route('profile.edit') }}" class="inline-block px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition">
                                    Activate a Game
                                </a>
                            </div>
                        @else
                            <div class="py-2">
                                @foreach($availableGames as $game)
                                <form method="POST" action="{{ route('current-game.update') }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="game_id" value="{{ $game->id }}">
                                    <button type="submit" class="w-full text-left px-4 py-2 text-gray-300 hover:bg-white/10 hover:text-white transition {{ $currentGame && $currentGame->id === $game->id ? 'bg-white/10 text-white font-semibold' : '' }}">
                                        <div class="flex items-center gap-2">
                                            @if($game->code === 'pokemon')
                                                <img src="/images/logos/logo_pokemon.png" alt="{{ $game->name }}" class="w-5 h-5 object-contain">
                                            @elseif($game->code === 'mtg')
                                                <span class="text-xs font-bold">MTG</span>
                                            @elseif($game->code === 'yugioh')
                                                <span class="text-xs font-bold">YGO</span>
                                            @endif
                                            <span class="flex-1">{{ $game->name }}</span>
                                            @if($currentGame && $currentGame->id === $game->id)
                                                <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    </button>
                                </form>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                
                <a href="{{ route('tcg.expansions.index') }}" class="flex items-center gap-2 px-3 py-2 text-gray-300 hover:text-white transition {{ request()->routeIs('tcg.expansions.*') ? 'text-white font-semibold' : '' }}">
                    <span>{{ __('catalogue.expansions_title') }}</span>
                </a>
                @endif
                
                @if(auth()->user()->hasRole('superadmin'))
                <a href="{{ route('admin.activitylog.index') }}" class="px-3 py-2 text-gray-300 hover:text-white transition {{ request()->routeIs('admin.activitylog.index') ? 'text-white font-semibold' : '' }}">
                    {{ __('messages.nav.activity_log') }}
                </a>
                <a href="{{ route('superadmin.plans.index') }}" class="px-3 py-2 text-gray-300 hover:text-white transition {{ request()->routeIs('superadmin.plans.index') ? 'text-white font-semibold' : '' }}">
                    {{ __('messages.nav.pricing_plans') }}
                </a>
                <a href="{{ route('admin.articles.index') }}" class="px-3 py-2 text-gray-300 hover:text-white transition {{ request()->routeIs('admin.articles.*') ? 'text-white font-semibold' : '' }}">
                    Articles
                </a>
                @endif
                
                @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('users.index') }}" class="px-3 py-2 text-gray-300 hover:text-white transition {{ request()->routeIs('users.index') ? 'text-white font-semibold' : '' }}">
                    {{ __('messages.nav.user_management') }}
                </a>
                @endif
                
            </div>
            
            <div class="hidden md:flex items-center gap-4">
                <!-- Global Card Search -->
                @if(! auth()->user()->hasRole('superadmin'))
                <div class="relative" x-data="{ searchOpen: false }" @click.away="searchOpen = false">
                    <input 
                        type="text" 
                        id="global-card-search" 
                        placeholder="Search cards..."
                        class="px-3 py-1.5 w-64 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 focus:outline-none focus:border-white/40 text-sm"
                        @focus="searchOpen = true"
                    >
                    <div id="search-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                        <!-- Results will be inserted here by JS -->
                    </div>
                </div>

                <!-- Collection Icon -->
                <a href="{{ route('collection.index') }}" class="p-2 rounded-lg hover:bg-white/10 text-gray-300 hover:text-white transition {{ request()->routeIs('collection.*') ? 'bg-white/10 text-white' : '' }}" title="{{ __('tcg/interactions.likes_title') }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </a>

                <!-- Like Icon -->
                <a href="{{ route('tcg.likes') }}" class="p-2 rounded-lg hover:bg-white/10 text-gray-300 hover:text-white transition {{ request()->routeIs('tcg.likes') ? 'bg-white/10 text-white' : '' }}" title="{{ __('tcg/interactions.likes_title') }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </a>

                <!-- Wishlist Icon -->
                <a href="{{ route('tcg.wishlist') }}" class="p-2 rounded-lg hover:bg-white/10 text-gray-300 hover:text-white transition {{ request()->routeIs('tcg.wishlist') ? 'bg-white/10 text-white' : '' }}" title="{{ __('tcg/interactions.wishlist_title') }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                </a>

                <!-- Watch Icon -->
                <a href="{{ route('tcg.osservazione') }}" class="p-2 rounded-lg hover:bg-white/10 text-gray-300 hover:text-white transition {{ request()->routeIs('tcg.osservazione') ? 'bg-white/10 text-white' : '' }}" title="{{ __('tcg/interactions.osservazione_title') }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </a>

                <!-- Decks Dropdown -->
                <div class="relative" x-data="{ decksOpen: false }">
                    <button @click="decksOpen = !decksOpen" class="p-2 rounded-lg hover:bg-white/10 text-gray-300 hover:text-white transition {{ request()->routeIs('decks.*') ? 'bg-white/10 text-white' : '' }}" title="My Decks">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div x-show="decksOpen" @click.away="decksOpen = false" x-cloak class="absolute right-0 mt-2 w-64 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto">
                        <div class="px-4 py-3 border-b border-white/10">
                            <h3 class="text-white font-semibold text-sm">My Decks</h3>
                        </div>
                        @php
                            $userDecks = Auth::user()->decks()->latest()->take(10)->get();
                        @endphp
                        @if($userDecks->isEmpty())
                            <div class="px-4 py-6 text-center">
                                <p class="text-gray-400 text-sm mb-3">No decks yet</p>
                                @if(Auth::user()->canCreateAnotherDeck())
                                    <a href="{{ route('decks.create') }}" class="inline-block px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition">
                                        Create Deck
                                    </a>
                                @else
                                    <a href="{{ route('profile.subscription') }}" class="inline-block px-3 py-1.5 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white text-sm rounded transition font-semibold">
                                        {{ __('decks/index.upgrade') }}
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="py-2">
                                @foreach($userDecks as $deck)
                                <a href="{{ route('decks.show', $deck) }}" class="block px-4 py-2 text-gray-300 hover:bg-white/10 hover:text-white transition">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium truncate">{{ $deck->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $deck->totalCards() }} cards</div>
                                        </div>
                                        @if($deck->format)
                                        <span class="ml-2 px-2 py-0.5 bg-purple-500/20 text-purple-300 text-xs rounded">{{ $deck->format }}</span>
                                        @endif
                                    </div>
                                </a>
                                @endforeach
                            </div>
                            <div class="border-t border-white/10 px-4 py-2">
                                <a href="{{ route('decks.index') }}" class="block text-center text-sm text-blue-400 hover:text-blue-300 transition">
                                    View All Decks
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="flex items-center gap-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff&size=32" alt="Avatar" class="h-8 w-8 rounded-full">
                    <span class="text-white font-medium">{{ Auth::user()->name }}</span>
                </div>
                
                <div class="relative">
                    <button class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-gray-300 font-medium focus:outline-none" @click="open = !open">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl z-10">
                        <form method="POST" action="{{ route('locale.switch') }}" class="px-4 py-2">
                            @csrf
                            <select name="locale" onchange="this.form.submit()" class="px-2 py-1 rounded bg-black/50 border-white/20 text-white w-full">
                                <option value="da" @if(app()->getLocale() == 'da') selected @endif>{{ __('messages.danish') }}</option>
                                <option value="en" @if(app()->getLocale() == 'en') selected @endif>{{ __('messages.english') }}</option>
                                <option value="it" @if(app()->getLocale() == 'it') selected @endif>{{ __('messages.italian') }}</option>
                            </select>
                        </form>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-300 hover:bg-white/10">{{ __('messages.Profile') }}</a>
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('billing.index') }}" class="block px-4 py-2 text-gray-300 hover:bg-white/10">{{ __('messages.Billing_Plans') }}</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-gray-300 hover:bg-white/10">{{ __('messages.Log_Out') }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="md:hidden flex items-center">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-300 hover:bg-white/10 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div :class="{'block': open, 'hidden': ! open}" class="md:hidden hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-gray-300 hover:bg-white/10">{{ __('messages.Dashboard') }}</a>
        </div>
        <div class="pt-4 pb-1 border-t border-white/10">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-400">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-300 hover:bg-white/10">{{ __('messages.Profile') }}</a>
                @if(Auth::user()->hasRole('admin'))
                    <a href="{{ route('billing.index') }}" class="block px-4 py-2 text-gray-300 hover:bg-white/10">{{ __('messages.Billing_Plans') }}</a>
                @endif
                <form method="POST" action="{{ route('locale.switch') }}" class="mb-3">
                    @csrf
                    <select name="locale" onchange="this.form.submit()" class="px-2 py-1 rounded bg-black/50 border-white/20 text-white w-full">
                        <option value="da" @if(app()->getLocale() == 'da') selected @endif>{{ __('messages.danish') }}</option>
                        <option value="en" @if(app()->getLocale() == 'en') selected @endif>{{ __('messages.english') }}</option>
                        <option value="it" @if(app()->getLocale() == 'it') selected @endif>{{ __('messages.italian') }}</option>
                    </select>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-300 hover:bg-white/10">{{ __('messages.Log_Out') }}</button>
                </form>
            </div>
        </div>
    </div>
    @endauth
</nav>

@auth
<script>
    // Theme switcher for desktop
    document.getElementById('theme-selector')?.addEventListener('change', function(e) {
        const theme = e.target.value;
        updateTheme(theme);
    });
    
    // Theme switcher for mobile
    document.getElementById('theme-selector-mobile')?.addEventListener('change', function(e) {
        const theme = e.target.value;
        updateTheme(theme);
    });
    
    function updateTheme(theme) {
        fetch('{{ route("user.theme.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ theme: theme })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Apply theme immediately and reload to ensure consistency
                document.documentElement.setAttribute('data-theme', theme);
                setTimeout(() => window.location.reload(), 100);
            }
        })
        .catch(error => console.error('Theme update failed:', error));
    }
</script>
@endauth
