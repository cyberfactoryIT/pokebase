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
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-white mb-4">Quick Actions</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- My Collection -->
                    <a href="{{ route('collection.index') }}" class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg p-6 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-purple-500/20 p-3 rounded-lg group-hover:bg-purple-500/30 transition">
                                <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold mb-1">My Collection</h4>
                                <p class="text-gray-400 text-sm">{{ $userCollectionCount }} card{{ $userCollectionCount != 1 ? 's' : '' }} ({{ $uniqueCardsCount }} unique)</p>
                            </div>
                        </div>
                    </a>

                    <!-- My Decks -->
                    <a href="{{ route('decks.index') }}" class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg p-6 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-green-500/20 p-3 rounded-lg group-hover:bg-green-500/30 transition">
                                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold mb-1">My Decks</h4>
                                <p class="text-gray-400 text-sm">{{ $userDecksCount }} deck{{ $userDecksCount != 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </a>

                    @if($expansionsCount > 0)
                    <a href="{{ route('tcg.expansions.index') }}" class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg p-6 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-yellow-500/20 p-3 rounded-lg group-hover:bg-yellow-500/30 transition">
                                <img src="/images/logos/logo_pokemon.png" alt="Pokemon" class="w-8 h-8 object-contain">
                            </div>
                            <div>
                                <h4 class="text-white font-semibold mb-1">Browse Expansions</h4>
                                <p class="text-gray-400 text-sm">Explore all card sets</p>
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
                                <p class="text-gray-400 text-sm">Use search bar above</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        

            <!-- Quick Stats -->
            @if($cardsCount > 0 || $expansionsCount > 0)
            <h3 class="font-semibold text-xl text-white mb-4">
                {{ $currentGame->name }}
                @php
                    $logoPath = "/images/logos/logo_{$currentGame->code}.png";
                @endphp
                @if(file_exists(public_path($logoPath)))
                    <img src="{{ $logoPath }}" alt="{{ $currentGame->name }}" class="inline w-6 h-6 object-contain">
                @endif
            </h3>
             
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

            <!-- Informational Articles -->
            @if($articles && $articles->isNotEmpty())
            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-xl text-white">Informational Articles</h3>
                    
                    <!-- Category Filter -->
                    @if($articleCategories && $articleCategories->isNotEmpty())
                    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <label class="text-sm text-gray-400">Filter:</label>
                        <select name="article_category" onchange="this.form.submit()" 
                            class="bg-white/5 border border-white/10 rounded-lg px-3 py-1 text-sm text-white focus:border-white/20">
                            <option value="">All Categories</option>
                            @foreach($articleCategories as $cat)
                                <option value="{{ $cat }}" {{ request('article_category') == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                        @if(request('article_category'))
                            <a href="{{ route('dashboard') }}" class="text-sm text-blue-400 hover:text-blue-300">Clear</a>
                        @endif
                    </form>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($articles as $article)
                    <div class="bg-white/5 border border-white/10 rounded-lg overflow-hidden hover:border-white/20 transition">
                        <!-- Category Badge -->
                        <div class="px-4 pt-4">
                            <span class="inline-block bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $article->category }}
                            </span>
                            
                            <!-- Language Badge -->
                            @if($article->isOriginalLocale($userLocale))
                            <span class="inline-block bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ strtoupper($article->original_locale) }}
                            </span>
                            @else
                            <span class="inline-block bg-yellow-500/20 text-yellow-400 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ strtoupper($userLocale) }} ← {{ strtoupper($article->original_locale) }}
                            </span>
                            @endif
                        </div>
                        
                        <!-- Image (if exists) -->
                        @if($article->image_path && file_exists(public_path($article->image_path)))
                        <div class="px-4 pt-3">
                            <img src="{{ asset($article->image_path) }}" alt="{{ $article->getTitleInLocale($userLocale) }}" class="w-full h-40 object-cover rounded-lg">
                        </div>
                        @endif
                        
                        <!-- Card Content -->
                        <div class="p-4">
                            <!-- Excerpt -->
                            <p class="text-gray-300 text-sm mb-4">{{ $article->getExcerptInLocale($userLocale) }}</p>
                            
                            <!-- Accordion Toggle (HTML5 details/summary) -->
                            <details class="group">
                                <summary class="cursor-pointer text-blue-400 hover:text-blue-300 font-semibold text-sm flex items-center gap-2 list-none">
                                    <span>Read more</span>
                                    <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </summary>
                                
                                <!-- Full Article Content -->
                                <div class="mt-4 pt-4 border-t border-white/10">
                                    <h4 class="text-white font-bold text-lg mb-3">{{ $article->getTitleInLocale($userLocale) }}</h4>
                                    
                                    <!-- Rendered Markdown Body -->
                                    <div class="prose prose-invert prose-sm max-w-none text-gray-300">
                                        {!! $article->getBodyHtmlInLocale($userLocale) !!}
                                    </div>
                                    
                                    <!-- External Link (if exists) -->
                                    @if($article->external_url)
                                    <div class="mt-4 pt-4 border-t border-white/10">
                                        <a href="{{ $article->external_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm font-semibold">
                                            <span>Open external source</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </details>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

         </div>   
    </div>
</div>
@endsection
