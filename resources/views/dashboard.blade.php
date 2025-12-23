@extends('layouts.app')

@section('content')

<div class="bg-black min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-6">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <!-- Header -->
            <div class="mb-8">
                <h2 class="font-semibold text-3xl text-white mb-2">
                    {{ __('messages.Dashboard') }}
                </h2>
                <p class="text-gray-400">
                    Explore the card catalog and browse through expansions.
                </p>
            </div>

            <!-- Welcome Message -->
            <div class="bg-green-900/30 border border-green-500/30 rounded-lg p-4 mb-8">
                <h3 class="font-semibold text-green-300 mb-2">{{ __('messages.welcome').' '.Auth::user()->name }}!</h3>
                <p class="text-green-200 text-sm">{{ __('messages.you_are_logged_in_correctly') }}</p>
            </div>

            <!-- Quick Actions -->
            <div>
                <h3 class="font-semibold text-xl text-white mb-4">Quick Actions</h3>
                
                @if($cardsCount > 0 || $expansionsCount > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($expansionsCount > 0)
                    <a href="{{ route('tcg.expansions.index') }}" class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg p-6 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-yellow-500/20 p-3 rounded-lg group-hover:bg-yellow-500/30 transition">
                                <img src="/images/logos/logo_pokemon.png" alt="Pokemon" class="w-8 h-8 object-contain">
                            </div>
                            <div>
                                <h4 class="text-white font-semibold mb-1">Browse Expansions</h4>
                                <p class="text-gray-400 text-sm">Explore all available card sets</p>
                            </div>
                        </div>
                    </a>
                    @endif

                    @if($cardsCount > 0)
                    <div class="block bg-white/5 border border-white/10 rounded-lg p-6">
                        <div class="flex items-center gap-4">
                            <div class="bg-blue-500/20 p-3 rounded-lg">
                                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold mb-1">Search Cards</h4>
                                <p class="text-gray-400 text-sm">Use the search bar above to find cards</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <!-- Empty State -->
                <div class="bg-white/5 border border-white/10 rounded-lg p-8 text-center">
                    <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-white font-semibold text-lg mb-2">No Content Yet</h3>
                    <p class="text-gray-400">The catalog is being prepared. Check back soon!</p>
                </div>
                @endif
            </div>
        

            <!-- Quick Stats -->
            @if($cardsCount > 0 || $expansionsCount > 0)
            <h3 class="font-semibold text-xl text-white mb-4">Pokemon Card <img src="/images/logos/logo_pokemon.png" alt="Pokemon" class="inline w-6 h-6 object-contain"></h3>
             
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                @if($cardsCount > 0)
                <div class="bg-white/5 border border-white/10 rounded-lg p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-500/20 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Total Cards</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($cardsCount) }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($expansionsCount > 0)
                <div class="bg-white/5 border border-white/10 rounded-lg p-6">
                    <div class="flex items-center gap-4">
                        <div class="bg-purple-500/20 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Total Expansions</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($expansionsCount) }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

         </div>   
    </div>
</div>
@endsection
