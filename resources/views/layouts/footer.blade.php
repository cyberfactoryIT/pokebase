<footer class="border-t border-white/10 py-12 px-6 lg:px-8 bg-black">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <!-- Logo and Brand -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo_basecard.svg') }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                <span class="text-xl font-bold">{{ config('app.name') }}</span>
            </div>
            
            <!-- Game Selector (only for authenticated users) -->
            @auth
            @if(! auth()->user()->hasRole('superadmin'))
            <div class="relative" x-data="{ gameOpen: false }">
                <button @click="gameOpen = !gameOpen" class="flex items-center gap-2 px-3 py-2 text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition">
                    @if(isset($currentGame) && $currentGame)
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
                
                <div x-show="gameOpen" @click.away="gameOpen = false" x-cloak class="absolute bottom-full left-0 mb-2 w-64 bg-[#1a1a19] border border-white/20 rounded-lg shadow-xl z-50">
                    @if(!isset($availableGames) || $availableGames->isEmpty())
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
            @endif
            @endauth
            
            <!-- Links -->
            <div class="flex gap-8 text-sm text-gray-400">
                <a href="{{ route('privacy') }}" class="hover:text-white transition">{{ __('footer.privacy_policy') }}</a>
                <a href="{{ route('terms') }}" class="hover:text-white transition">{{ __('footer.terms_of_service') }}</a>
                <a href="{{ route('handelsbetingelser') }}" class="hover:text-white transition">Handelsbetingelser</a>
                <a href="{{ route('contact') }}" class="hover:text-white transition">{{ __('footer.contact') }}</a>
            </div>
        </div>
        
        <!-- Company Info -->
        <div class="mt-8 pt-8 border-t border-white/10 text-center text-sm text-gray-400">
            <span class="font-semibold">{{ config('invoice.biller_name') }}</span>
            <span class="mx-2">•</span>
            <span>{{ config('invoice.biller_address') }}</span>
            <span class="mx-2">•</span>
            <span>{{ config('invoice.biller_vat') }}</span>
            <span class="mx-2">•</span>
            <a href="mailto:{{ config('invoice.biller_email') }}" class="hover:text-white transition">{{ config('invoice.biller_email') }}</a>
            <span class="mx-2">•</span>
            <a href="tel:{{ config('invoice.biller_phone') }}" class="hover:text-white transition">{{ config('invoice.biller_phone') }}</a>
        </div>

        <!-- Copyright -->
        <div class="mt-4 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('footer.all_rights_reserved') }}
        </div>
    </div>
</footer>
