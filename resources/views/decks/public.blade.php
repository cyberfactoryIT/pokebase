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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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

            <!-- Unique Cards -->
            <div class="bg-[#161615] border border-white/15 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Unique Cards</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $stats['unique_cards'] }}</p>
                    </div>
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </div>
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
                <div class="grid grid-cols-12 gap-4 px-6 py-4 hover:bg-white/5 transition group">
                    <!-- Quantity -->
                    <div class="col-span-1 flex items-center">
                        <span class="text-white font-semibold text-lg">{{ $deckCard->quantity }}×</span>
                    </div>

                    <!-- Card Info -->
                    <div class="col-span-5 flex items-center gap-4">
                        @if($card->image_small)
                        <div class="relative group/image">
                            <img 
                                src="{{ $card->image_small }}" 
                                alt="{{ $card->name }}"
                                class="w-12 h-16 rounded object-cover border border-white/15"
                                loading="lazy"
                            >
                            <!-- Hover Preview -->
                            <div class="absolute left-full ml-4 top-0 z-50 opacity-0 group-hover/image:opacity-100 transition-opacity pointer-events-none">
                                <img 
                                    src="{{ $card->image_large }}" 
                                    alt="{{ $card->name }}"
                                    class="w-64 rounded-lg shadow-2xl border-2 border-white/30"
                                >
                            </div>
                        </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium group-hover:text-blue-400 transition truncate">
                                {{ $card->name }}
                            </p>
                            @if($card->supertype)
                            <p class="text-gray-500 text-sm">{{ $card->supertype }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Set -->
                    <div class="col-span-2 flex items-center">
                        <span class="text-gray-300 text-sm">{{ $card->set_name ?? '-' }}</span>
                    </div>

                    <!-- Rarity -->
                    <div class="col-span-2 flex items-center">
                        @if($card->rarity)
                        <span class="px-2 py-1 bg-purple-500/20 text-purple-300 text-xs rounded">
                            {{ $card->rarity }}
                        </span>
                        @else
                        <span class="text-gray-500 text-sm">-</span>
                        @endif
                    </div>

                    <!-- Number -->
                    <div class="col-span-2 flex items-center">
                        <span class="text-gray-400 text-sm">{{ $card->number ?? '-' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer CTA for Logged Users -->
        @auth
        <div class="mt-8 bg-gradient-to-r from-blue-600/10 to-purple-600/10 border border-blue-500/20 rounded-xl p-6 text-center">
            <h3 class="text-white font-semibold text-xl mb-2">{{ __('sharing.public.footer_cta_title') }}</h3>
            <p class="text-gray-400 mb-4">{{ __('sharing.public.footer_cta_body') }}</p>
            <a href="{{ route('decks.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('sharing.public.footer_cta_button') }}
            </a>
        </div>
        @endauth
    </div>
</div>
@endsection
