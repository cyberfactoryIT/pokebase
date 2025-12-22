@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('tcg.expansions.show', $card->group_id) }}" class="inline-flex items-center text-blue-400 hover:text-blue-300">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('catalogue.back_to') }} {{ $card->group->name ?? __('catalogue.expansion') }}
            </a>
        </div>

        <!-- Card Detail Layout (Scrydex-like) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Card Image -->
            <div class="space-y-6">
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <div class="aspect-[245/342] max-w-md mx-auto">
                        @if($imageUrl)
                            <img 
                                src="{{ $imageUrl }}" 
                                alt="{{ $card->name }}"
                                class="w-full h-full object-contain rounded-lg shadow-lg"
                                onerror="this.src='https://via.placeholder.com/490x684/1a1a19/666?text=No+Image'"
                            >
                        @else
                            <div class="w-full h-full bg-black/50 rounded-lg flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Additional Details -->
                @if($card->raw && is_array($card->raw))
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">{{ __('catalogue.additional_details') }}</h2>
                        
                        <dl class="space-y-3">
                            @foreach($card->raw as $key => $value)
                                @if(!is_array($value) && !is_object($value) && !in_array($key, ['raw', 'extended_data', 'extendedData']))
                                    <div class="flex justify-between py-2 border-b border-white/10">
                                        <dt class="text-sm font-medium text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                                        <dd class="text-sm text-white">{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>

            <!-- Right Column: Card Details -->
            <div class="space-y-6">
                <!-- Card Header -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $card->name }}</h1>
                    
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-300 mb-4">
                        @if($card->group)
                            <a href="{{ route('tcg.expansions.show', $card->group_id) }}" class="inline-flex items-center hover:text-blue-400">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                {{ $card->group->name }}
                            </a>
                        @endif
                        
                        @if($card->card_number)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/10 border border-white/20 text-gray-200">
                                #{{ $card->card_number }}
                            </span>
                        @endif
                        
                        @if($card->rarity)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 border border-purple-400/30 text-purple-300">
                                {{ $card->rarity }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">{{ __('catalogue.pricing') }}</h2>
                    
                    @if($latestPrice)
                        <div class="grid grid-cols-2 gap-4">
                            @if($latestPrice->market_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('catalogue.market_price') }}</div>
                                    <div class="text-2xl font-bold text-white">${{ number_format($latestPrice->market_price, 2) }}</div>
                                </div>
                            @endif
                            
                            @if($latestPrice->low_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('catalogue.low_price') }}</div>
                                    <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->low_price, 2) }}</div>
                                </div>
                            @endif
                            
                            @if($latestPrice->mid_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('catalogue.mid_price') }}</div>
                                    <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->mid_price, 2) }}</div>
                                </div>
                            @endif
                            
                            @if($latestPrice->high_price)
                                <div class="border border-white/20 bg-black/30 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 uppercase">{{ __('catalogue.high_price') }}</div>
                                    <div class="text-xl font-semibold text-gray-200">${{ number_format($latestPrice->high_price, 2) }}</div>
                                </div>
                            @endif
                        </div>
                        
                        @if($latestPrice->printing)
                            <div class="mt-3 text-sm text-gray-300">
                                <span class="font-medium">{{ __('catalogue.printing') }}:</span> {{ $latestPrice->printing }}
                            </div>
                        @endif
                        
                        <div class="mt-3 text-xs text-gray-400">
                            {{ __('catalogue.last_updated') }}: {{ $latestPrice->snapshot_at->diffForHumans() }}
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>{{ __('catalogue.pricing_coming_soon') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Extended Data -->
                @if($card->extended_data && is_array($card->extended_data) && count($card->extended_data) > 0)
                    <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">{{ __('catalogue.extended_information') }}</h2>
                        
                        <dl class="space-y-3">
                            @foreach($card->extended_data as $item)
                                @if(isset($item['name']) && isset($item['value']))
                                    <div class="flex justify-between py-2 border-b border-white/10">
                                        <dt class="text-sm font-medium text-gray-400">{{ $item['name'] }}</dt>
                                        <dd class="text-sm text-white">{{ $item['value'] }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
