@extends('layouts.app')

@section('content')

<div class="bg-black min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-6">
        <!-- Welcome Header with License Badge and Quick Actions -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-xl text-white">
                {{ __('messages.welcome').' '.Auth::user()->name }}!
            </h2>
            
            <div class="flex items-center gap-3">
                <!-- Quick Actions Icons -->
                <a href="{{ route('collection.index') }}" class="flex items-center gap-1.5 bg-purple-500/10 hover:bg-purple-500/20 border border-purple-500/20 rounded-lg px-2 py-1.5 transition group" title="{{ __('dashboard.my_collection') }}">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="text-white text-xs font-medium">{{ $userCollectionCount }}</span>
                </a>

                <a href="{{ route('decks.index') }}" class="flex items-center gap-1.5 bg-green-500/10 hover:bg-green-500/20 border border-green-500/20 rounded-lg px-2 py-1.5 transition group" title="{{ __('dashboard.my_decks') }}">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="text-white text-xs font-medium">{{ $userDecksCount }}</span>
                </a>

                @if($expansionsCount > 0)
                @php
                    $setsUrl = match($catalogBackend) {
                        'tcgdex' => route('pokemon.sets'),
                        'cmapi' => route('cmapi.sets.index', ['game' => $currentGame->slug ?? 'lorcana']),
                        default => route('tcg.expansions.index')
                    };
                @endphp
                <a href="{{ $setsUrl }}" class="flex items-center gap-1.5 bg-yellow-500/10 hover:bg-yellow-500/20 border border-yellow-500/20 rounded-lg px-2 py-1.5 transition group" title="{{ __('dashboard.browse_expansions') }}">
                    <img src="/images/logos/logo_pokemon.png" alt="Pokemon" class="w-4 h-4 object-contain">
                    <span class="text-white text-xs font-medium">{{ $expansionsCount }}</span>
                </a>
                @endif

                <!-- Divider -->
                <div class="w-px h-6 bg-white/10"></div>

                <!-- License Badge -->
                @php
                    $user = Auth::user();
                    $isPremium = $user->organization && $user->organization->pricingPlan && $user->organization->pricingPlan->slug === 'premium';
                    $planName = $isPremium ? 'Premium' : ($user->organization && $user->organization->pricingPlan ? $user->organization->pricingPlan->name : 'Free');
                @endphp
                
                <div class="flex items-center gap-1.5 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5">
                    <svg class="w-4 h-4 {{ $isPremium ? 'text-yellow-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="text-white text-sm font-medium">{{ $planName }}</span>
                </div>
                
                @if(!$isPremium)
                    <a href="{{ route('pricing') }}" class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Upgrade
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">

            <!-- 2 Column Layout: Left (66%) stacked Quick Search + Recent + Expansions, Right (33%) Top Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Left Column: Quick Search + Recent Additions + Featured Expansions stacked (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Quick Search Card -->
                    @include('dashboard.quick-add')

                    <!-- Recent Additions -->
                    @if($catalogBackend === 'tcgdex')
                        @include('dashboard.tcgdex.recent-additions')
                    @elseif($catalogBackend === 'cmapi')
                        @include('dashboard.cmapi.recent-additions')
                    @else
                        @include('dashboard.tcgcsv.recent-additions')
                    @endif

                    <!-- Featured Expansions Carousel -->
                    @if($catalogBackend === 'tcgdex')
                        @include('dashboard.tcgdex.featured-expansions')
                    @elseif($catalogBackend === 'cmapi')
                        @include('dashboard.cmapi.featured-expansions')
                    @else
                        @include('dashboard.tcgcsv.featured-expansions')
                    @endif
                </div>

                <!-- Right Column: Top Valuable Cards (1/3 width) -->
                <div class="lg:col-span-1">
                    @if($catalogBackend === 'tcgdex')
                        @include('dashboard.tcgdex.top-cards')
                    @elseif($catalogBackend === 'cmapi')
                        @include('dashboard.cmapi.top-cards')
                    @else
                        @include('dashboard.tcgcsv.top-cards')
                    @endif
                </div>
            </div>

            <!-- Missing Cards (Full Width) -->
            @if($catalogBackend === 'tcgdex')
                @include('dashboard.tcgdex.missing-cards')
            @elseif($catalogBackend === 'cmapi')
                @include('dashboard.cmapi.missing-cards')
            @else
                @include('dashboard.tcgcsv.missing-cards')
            @endif
        

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
                            <p class="text-gray-400 text-sm">{{ __('dashboard.total_cards') }}</p>
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
                            <p class="text-gray-400 text-sm">{{ __('dashboard.total_expansions') }}</p>
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
                    <h3 class="font-semibold text-xl text-white">{{ __('dashboard.articles_section') }}</h3>
                    
                    <!-- Category Filter -->
                    @if($articleCategories && $articleCategories->isNotEmpty())
                    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <label class="text-sm text-gray-400">{{ __('messages.filter') }}:</label>
                        <select name="article_category" onchange="this.form.submit()" 
                            class="bg-white/5 border border-white/10 rounded-lg px-3 py-1 text-sm text-white focus:border-white/20">
                            <option value="">{{ __('dashboard.all_categories') }}</option>
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
                                    <span>{{ __('dashboard.read_more') }}</span>
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
                                            <span>{{ __('dashboard.open_external') }}</span>
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
