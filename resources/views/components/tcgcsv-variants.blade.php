@props(['product'])

@php
    $variants = $product->getTcgcsvVariants();
@endphp

@if($variants->isEmpty())
    <div class="text-sm text-gray-400 italic">
        {{ __('variants.no_other_printings') }}
    </div>
@else
    <div class="space-y-3">
        @foreach($variants as $variant)
            @php
                $price = $variant->prices->first();
                $printing = $price?->printing ?? 'Normal';
            @endphp
            
            <a href="{{ route('tcg.cards.show', $variant->product_id) }}" 
               class="block bg-black/30 hover:bg-black/50 border border-white/10 hover:border-blue-500/30 rounded-lg p-4 transition group">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-white font-medium group-hover:text-blue-400">{{ $printing }}</span>
                            
                            @if(str_contains(strtolower($printing), 'reverse') || str_contains(strtolower($printing), 'holo'))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md border bg-purple-500/20 text-purple-300 border-purple-500/30">
                                    <span>✨</span>
                                    {{ __('variants.reverse_holo') }}
                                </span>
                            @elseif(str_contains(strtolower($printing), '1st'))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md border bg-yellow-500/20 text-yellow-300 border-yellow-500/30">
                                    <span>1st</span>
                                    {{ __('variants.first_edition') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md border bg-blue-500/20 text-blue-300 border-blue-500/30">
                                    {{ __('variants.normal') }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="text-xs text-gray-400">
                            {{ __('variants.product_id') }}: {{ $variant->product_id }}
                        </div>
                    </div>
                    
                    <div class="text-right">
                        @if($price && $price->market_price)
                            <div class="text-green-400 font-semibold text-lg">
                                ${{ number_format($price->market_price, 2) }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ __('variants.market_price') }}
                            </div>
                        @else
                            <div class="text-gray-500 text-sm">
                                {{ __('variants.price_not_available') }}
                            </div>
                        @endif
                    </div>
                    
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        @endforeach
    </div>
@endif
