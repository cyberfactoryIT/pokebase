@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Header -->
        <div class="mb-6">
            <!-- Public View Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-500/20 text-blue-300 rounded-lg text-sm mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                {{ __('sharing.public.view_only') }}
            </div>

            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $deck->name }}</h1>
                    <div class="flex items-center gap-4 text-gray-400">
                        @if($deck->format)
                        <span class="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded">{{ $deck->format }}</span>
                        @endif
                        <span>{{ $deck->totalCards() }} cards</span>
                        <span>Shared {{ $deck->shared_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            @if($deck->description)
            <p class="text-gray-400 mt-4">{{ $deck->description }}</p>
            @endif
        </div>

        <!-- CTA for Anonymous Users -->
        @guest
        <div class="bg-gradient-to-r from-blue-600/20 to-purple-600/20 border border-blue-500/30 rounded-xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="bg-blue-500/20 p-3 rounded-lg flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-white font-semibold text-lg mb-2">{{ __('sharing.public.cta_title') }}</h3>
                    <p class="text-gray-300 mb-4">{{ __('sharing.public.cta_body') }}</p>
                    <div class="flex gap-3">
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                            {{ __('sharing.public.cta_register') }}
                        </a>
                        <a href="{{ route('login') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-gray-300 rounded-lg transition">
                            {{ __('sharing.public.cta_login') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endguest

        <!-- Deck Statistics (No Prices for Public View) -->
        @if(!$deck->deckCards->isEmpty())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Cards -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Cards</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['total_cards'] }}</p>
                    </div>
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Value -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Estimated Value</p>
                        <p class="text-3xl font-bold text-white mt-1">
                            @if($stats['total_value'] > 0)
                                @php
                                    $symbol = \App\Services\CurrencyService::getSymbol($stats['currency']);
                                    $formatted = number_format($stats['total_value'], 2);
                                    if (in_array($stats['currency'], ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'])) {
                                        echo "{$symbol}{$formatted}";
                                    } else {
                                        echo "{$formatted} {$symbol}";
                                    }
                                @endphp
                            @else
                                <span class="text-gray-500">No prices</span>
                            @endif
                        </p>
                        @if($stats['cards_with_prices'] > 0)
                            <p class="text-gray-500 text-xs mt-1">{{ $stats['cards_with_prices'] }} cards priced</p>
                        @endif
                    </div>
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="8" cy="8" r="4" opacity="0.6"/>
                            <circle cx="12" cy="12" r="4" opacity="0.8"/>
                            <circle cx="16" cy="16" r="4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Rarity Distribution -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <h3 class="text-gray-400 text-sm mb-3">Rarity Distribution</h3>
                <div class="space-y-2">
                    @forelse($topStats['rarity_distribution'] as $rarity => $data)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300 text-sm">{{ $rarity }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-white font-semibold">{{ $data['total_quantity'] }}</span>
                                <span class="text-gray-500 text-xs">({{ $data['count'] }} unique)</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No rarity data</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <!-- Deck Cards Grid -->
        @if($deck->deckCards->isEmpty())
        <div class="bg-[#161615] border border-white/15 rounded-xl p-12 text-center">
            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="text-white text-xl font-semibold mb-2">No Cards Yet</h3>
            <p class="text-gray-400">This deck is empty.</p>
        </div>
        @else
        <div class="bg-[#161615] border border-white/15 rounded-xl overflow-hidden">
            <!-- Cards Table Header -->
            <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-black/50 border-b border-white/10 text-gray-400 text-sm font-semibold">
                <div class="col-span-1">QTY</div>
                <div class="col-span-5">CARD</div>
                <div class="col-span-2">SET</div>
                <div class="col-span-2">RARITY</div>
                <div class="col-span-2">NUMBER</div>
            </div>

            <!-- Cards List -->
            <div class="divide-y divide-white/10">
                @foreach($deck->deckCards as $deckCard)
                @php
                    $card = $deckCard->product;
                @endphp
                
                @if($card)
                <div class="grid grid-cols-12 gap-4 px-6 py-4 hover:bg-white/5 transition group">
                    <!-- Quantity -->
                    <div class="col-span-1 flex items-center">
                        <span class="text-white font-semibold text-lg">{{ $deckCard->quantity }}×</span>
                    </div>

                    <!-- Card Info -->
                    <div class="col-span-5 flex items-center gap-4">
                        @php
                            // Check for user-uploaded photos first, then fall back to card images
                            $hasPhotos = $deckCard->photos->count() > 0;
                            
                            // Handle different backend image formats
                            $cardImageSmall = null;
                            $cardImageLarge = null;
                            
                            if (isset($card->image_small)) {
                                // TCGCSV format (string properties)
                                $cardImageSmall = $card->image_small;
                                $cardImageLarge = $card->image_large ?? $card->image_small;
                            } elseif (isset($card->image_small_url)) {
                                // TCGDEX format (string URL properties with /low.webp and /high.webp)
                                $cardImageSmall = $card->getLowQualityImageUrl();
                                $cardImageLarge = $card->getHighQualityImageUrl();
                            } elseif (isset($card->images) && is_array($card->images)) {
                                // CMAPI format (array)
                                $cardImageSmall = $card->images['small'] ?? null;
                                $cardImageLarge = $card->images['large'] ?? $card->images['small'] ?? null;
                            }
                            
                            $primaryImage = $hasPhotos 
                                ? route('decks.photos.serve', $deckCard->photos->first())
                                : $cardImageSmall;
                            $hoverImage = $hasPhotos
                                ? route('decks.photos.serve', $deckCard->photos->first())
                                : $cardImageLarge;
                            
                            // Handle multilingual fields (TCGDEX returns arrays)
                            $cardName = $card->name;
                            if (is_array($cardName)) {
                                $cardName = $cardName['en'] ?? $cardName['da'] ?? $cardName['fr'] ?? 'Unknown';
                            }
                            
                            $cardType = $card->supertype ?? $card->type ?? null;
                            if (is_array($cardType)) {
                                $cardType = $cardType['en'] ?? $cardType['da'] ?? '';
                            }
                            
                            // Handle set names from different backends
                            $setName = null;
                            if (isset($card->set_name)) {
                                // TCGCSV format (direct property)
                                $setName = $card->set_name;
                            } elseif (isset($card->set) && $card->set) {
                                // TCGDEX/CMAPI format (relationship)
                                $setNameData = $card->set->name ?? null;
                                if (is_array($setNameData)) {
                                    // Multilingual (TCGDEX)
                                    $setName = $setNameData['en'] ?? $setNameData['da'] ?? $setNameData['fr'] ?? null;
                                } else {
                                    // String (CMAPI)
                                    $setName = $setNameData;
                                }
                            }
                            $setName = $setName ?? '-';
                        @endphp
                        
                        @if($primaryImage)
                        <div class="relative group/image">
                            <img 
                                src="{{ $primaryImage }}" 
                                alt="{{ $cardName }}"
                                class="w-12 h-16 rounded object-cover border border-white/15 {{ $hasPhotos ? 'ring-2 ring-blue-500/50' : '' }}"
                                loading="lazy"
                            >
                            <!-- Photo count badge -->
                            @if($hasPhotos && $deckCard->photos->count() > 1)
                            <div class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                {{ $deckCard->photos->count() }}
                            </div>
                            @endif
                            
                            <!-- Hover Preview -->
                            <div class="absolute left-full ml-4 top-0 z-50 opacity-0 group-hover/image:opacity-100 transition-opacity pointer-events-none">
                                @if($hasPhotos)
                                    <!-- Show all uploaded photos -->
                                    <div class="flex gap-2">
                                        @foreach($deckCard->photos as $photo)
                                        <img 
                                            src="{{ route('decks.photos.serve', $photo) }}" 
                                            alt="{{ $cardName }}"
                                            class="w-64 rounded-lg shadow-2xl border-2 border-blue-500/50"
                                        >
                                        @endforeach
                                    </div>
                                @else
                                    <!-- Show card image -->
                                    <img 
                                        src="{{ $hoverImage }}" 
                                        alt="{{ $cardName }}"
                                        class="w-64 rounded-lg shadow-2xl border-2 border-white/30"
                                    >
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium group-hover:text-blue-400 transition truncate">
                                {{ $cardName }}
                            </p>
                            @if($cardType)
                            <p class="text-gray-500 text-sm">{{ $cardType }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Set -->
                    <div class="col-span-2 flex items-center">
                        <span class="text-gray-300 text-sm">{{ $setName ?? '-' }}</span>
                    </div>

                    <!-- Rarity -->
                    <div class="col-span-2 flex items-center">
                        @if($card->rarity ?? null)
                        <span class="px-2 py-1 bg-purple-500/20 text-purple-300 text-xs rounded">
                            {{ $card->rarity }}
                        </span>
                        @else
                        <span class="text-gray-500 text-sm">-</span>
                        @endif
                    </div>

                    <!-- Number -->
                    <div class="col-span-2 flex items-center">
                        <span class="text-gray-400 text-sm">{{ $card->number ?? $card->local_id ?? '-' }}</span>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer CTA for Logged Users -->
        @auth
        <div class="mt-8 bg-gradient-to-r from-blue-600/10 to-purple-600/10 border border-blue-500/20 rounded-xl p-6 text-center">
            <h3 class="text-white font-semibold text-xl mb-2">{{ __('sharing.public.footer_cta_title') }}</h3>
            <p class="text-gray-400 mb-4">{{ __('sharing.public.footer_cta_body') }}</p>
            @if(Auth::user()->canCreateAnotherDeck())
                <a href="{{ route('decks.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('sharing.public.footer_cta_button') }}
                </a>
            @else
                <a href="{{ route('profile.subscription') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white rounded-lg transition font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    {{ __('decks/index.upgrade') }}
                </a>
            @endif
        </div>
        @endauth
    </div>
</div>
@endsection
