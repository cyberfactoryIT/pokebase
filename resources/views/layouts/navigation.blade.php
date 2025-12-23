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
                @if(! auth()->user()->hasRole('superadmin'))
                <a href="{{ route('tcg.expansions.index') }}" class="flex items-center gap-2 px-3 py-2 text-gray-300 hover:text-white transition {{ request()->routeIs('tcg.expansions.*') ? 'text-white font-semibold' : '' }}">
                    <img src="/images/logos/logo_pokemon.png" alt="Pokemon" class="w-5 h-5 object-contain">
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
