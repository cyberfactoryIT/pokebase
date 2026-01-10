@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Unmapped Collection Cards</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Cards in user collections without CardMarket mapping</p>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total in Collections</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_in_collections']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">Unmapped Cards</div>
                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($stats['unmapped_count']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">Mapped Cards</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['mapped_count']) }}</div>
            </div>
        </div>

        {{-- Search --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('superadmin.unmapped-collection.index') }}" class="flex gap-4">
                <div class="flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by card name or number..." 
                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('superadmin.unmapped-collection.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Cards Grid --}}
        @if($unmappedCards->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                @foreach($unmappedCards as $card)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                        {{-- Card Image --}}
                        <div class="aspect-[3/4] bg-gray-100 dark:bg-gray-700 relative">
                            @if($card->image_url)
                                <img src="{{ $card->image_url }}" 
                                     alt="{{ $card->name }}" 
                                     class="w-full h-full object-contain"
                                     loading="lazy">
                            @else
                                <div class="flex items-center justify-center h-full">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Card Info --}}
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 line-clamp-2" title="{{ $card->name }}">
                                {{ $card->name }}
                            </h3>
                            
                            @if($card->group)
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                                    {{ $card->group->name }}
                                </p>
                            @endif
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                                <span class="font-mono">#{{ $card->card_number }}</span>
                                @if($card->rarity)
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">{{ $card->rarity }}</span>
                                @endif
                            </div>
                            
                            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                <div><span class="font-semibold">Product ID:</span> {{ $card->product_id }}</div>
                                <div><span class="font-semibold">Group ID:</span> {{ $card->group_id }}</div>
                            </div>
                            
                            @if($card->prices->isNotEmpty())
                                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                    @php $price = $card->prices->first(); @endphp
                                    @if($price->market_price)
                                        <div class="text-sm font-semibold text-green-600 dark:text-green-400">
                                            ${{ number_format($price->market_price, 2) }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Pagination --}}
            <div class="mt-6">
                {{ $unmappedCards->links() }}
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No unmapped cards found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if(request('search'))
                        Try adjusting your search criteria.
                    @else
                        All cards in collections have CardMarket mappings!
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
