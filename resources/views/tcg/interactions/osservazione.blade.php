@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-white mb-2">{{ __('tcg/interactions.osservazione_title') }}</h1>
            <p class="text-gray-400">{{ __('tcg/interactions.osservazione_subtitle', ['count' => $watchedProducts->total()]) }}</p>
        </div>

        @if($watchedProducts->isEmpty())
            <!-- Empty State -->
            <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">{{ __('tcg/interactions.no_watching') }}</h3>
                <p class="text-gray-400 mb-6">{{ __('tcg/interactions.no_watching_description') }}</p>
                <a href="{{ route('tcg.expansions.index') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    {{ __('tcg/interactions.browse_cards') }}
                </a>
            </div>
        @else
            <!-- Cards Grid -->
            <div class="grid gap-3 mb-6" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                @foreach($watchedProducts as $product)
                    <div class="bg-[#1a1a19] border border-white/10 rounded-lg hover:border-white/30 hover:shadow-xl transition overflow-hidden group relative">
                        
                        <!-- Stop Watching Button -->
                        <div class="absolute top-2 right-2 z-10">
                            <form action="{{ route('tcg.items.watch', $product->product_id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 bg-blue-500/90 hover:bg-blue-600 rounded-full text-white transition" title="{{ __('tcg/interactions.stop_watching') }}">
                                    <svg class="w-4 h-4" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        
                        <a href="{{ route('tcg.cards.show', $product->product_id) }}">
                            <div class="aspect-[245/342] bg-black/50 overflow-hidden">
                                @php
                                    $imageUrl = $product->rapidapiCard->image_url ?? $product->image_url ?? 'https://via.placeholder.com/245x342/1a1a19/666?text=No+Image';
                                @endphp
                                <img 
                                    src="{{ $imageUrl }}" 
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    loading="lazy"
                                >
                            </div>
                            <div class="p-2">
                                <h3 class="text-xs font-semibold text-white truncate group-hover:text-blue-400 transition">
                                    {{ $product->name }}
                                </h3>
                                <div class="flex items-center justify-between mt-0.5">
                                    @if($product->card_number)
                                        <p class="text-xs text-gray-400">#{{ $product->card_number }}</p>
                                    @else
                                        <span></span>
                                    @endif
                                    @if($product->group)
                                        <span class="text-xs text-gray-500">{{ $product->group->abbreviation }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $watchedProducts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
