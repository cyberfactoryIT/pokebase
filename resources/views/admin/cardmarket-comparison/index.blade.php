@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">CardMarket Mapping Comparison</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Compare CardMarket product IDs between TCGCSV and TCGdex</p>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-xs text-gray-600 dark:text-gray-400">Total with TCGdex</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_with_tcgdex']) }}</div>
            </div>
            
            <a href="{{ route('superadmin.cardmarket-comparison.index', ['filter' => 'conflicts']) }}" 
               class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:ring-2 hover:ring-red-500 transition {{ $filter === 'conflicts' ? 'ring-2 ring-red-500' : '' }}">
                <div class="text-xs text-gray-600 dark:text-gray-400">⚠️ Conflicts</div>
                <div class="text-xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['conflicts']) }}</div>
            </a>
            
            <a href="{{ route('superadmin.cardmarket-comparison.index', ['filter' => 'tcgcsv_only']) }}" 
               class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:ring-2 hover:ring-blue-500 transition {{ $filter === 'tcgcsv_only' ? 'ring-2 ring-blue-500' : '' }}">
                <div class="text-xs text-gray-600 dark:text-gray-400">TCGCSV Only</div>
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['tcgcsv_only']) }}</div>
            </a>
            
            <a href="{{ route('superadmin.cardmarket-comparison.index', ['filter' => 'tcgdex_only']) }}" 
               class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:ring-2 hover:ring-orange-500 transition {{ $filter === 'tcgdex_only' ? 'ring-2 ring-orange-500' : '' }}">
                <div class="text-xs text-gray-600 dark:text-gray-400">TCGdex Only</div>
                <div class="text-xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($stats['tcgdex_only']) }}</div>
            </a>
            
            <a href="{{ route('superadmin.cardmarket-comparison.index', ['filter' => 'both']) }}" 
               class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:ring-2 hover:ring-green-500 transition {{ $filter === 'both' ? 'ring-2 ring-green-500' : '' }}">
                <div class="text-xs text-gray-600 dark:text-gray-400">✅ Both Same</div>
                <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['both_same']) }}</div>
            </a>
            
            <a href="{{ route('superadmin.cardmarket-comparison.index', ['filter' => 'neither']) }}" 
               class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:ring-2 hover:ring-gray-500 transition {{ $filter === 'neither' ? 'ring-2 ring-gray-500' : '' }}">
                <div class="text-xs text-gray-600 dark:text-gray-400">❌ Neither</div>
                <div class="text-xl font-bold text-gray-900 dark:text-gray-400">{{ number_format($stats['both_null']) }}</div>
            </a>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('superadmin.cardmarket-comparison.index') }}" class="flex gap-4">
                <input type="hidden" name="filter" value="{{ $filter }}">
                
                <div class="flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by card name or number..." 
                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Search
                </button>
                
                @if(request('search'))
                    <a href="{{ route('superadmin.cardmarket-comparison.index', ['filter' => $filter]) }}" 
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                        Clear
                    </a>
                @endif
                
                @if($filter !== 'all')
                    <a href="{{ route('superadmin.cardmarket-comparison.index') }}" 
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                        Show All
                    </a>
                @endif
            </form>
        </div>

        {{-- Results Table --}}
        @if($products->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Card</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Set</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">TCGCSV<br>CardMarket ID</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">TCGdex<br>CardMarket ID</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($products as $product)
                                @php
                                    $tcgcsvId = $product->cardmarket_product_id;
                                    $tcgdexId = $product->tcgdxCard?->cardmarket_product_id;
                                    
                                    if ($tcgcsvId && $tcgdexId && $tcgcsvId !== $tcgdexId) {
                                        $status = 'conflict';
                                        $statusColor = 'red';
                                        $statusIcon = '⚠️';
                                        $statusText = 'Conflict';
                                    } elseif ($tcgcsvId && !$tcgdexId) {
                                        $status = 'tcgcsv_only';
                                        $statusColor = 'yellow';
                                        $statusIcon = '⚡';
                                        $statusText = 'TCGCSV Only';
                                    } elseif (!$tcgcsvId && $tcgdexId) {
                                        $status = 'tcgdex_only';
                                        $statusColor = 'orange';
                                        $statusIcon = '🔶';
                                        $statusText = 'TCGdex Only';
                                    } elseif ($tcgcsvId && $tcgdexId && $tcgcsvId === $tcgdexId) {
                                        $status = 'match';
                                        $statusColor = 'green';
                                        $statusIcon = '✅';
                                        $statusText = 'Match';
                                    } else {
                                        $status = 'neither';
                                        $statusColor = 'gray';
                                        $statusIcon = '❌';
                                        $statusText = 'Neither';
                                    }
                                @endphp
                                
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-16 w-12 object-contain rounded mr-3">
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">#{{ $product->card_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $product->group?->abbreviation ?? $product->group?->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($tcgcsvId)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $tcgcsvId }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($tcgdexId)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                {{ $tcgdexId }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 dark:bg-{{ $statusColor }}-900 dark:text-{{ $statusColor }}-200">
                                            {{ $statusIcon }} {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $products->appends(['filter' => $filter, 'search' => request('search')])->links() }}
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400 text-lg">No products found with the current filter.</p>
            </div>
        @endif
    </div>
</div>
@endsection
