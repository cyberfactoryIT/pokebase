@extends('layouts.app')

@section('content')

<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-6">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-3xl text-white mb-2">
                        <i class="fa fa-crown text-yellow-400 mr-2"></i>
                        {{ __('messages.superadmin_dashboard') }}
                    </h2>
                    <p class="text-gray-400">
                        {{ __('messages.superadmin_dashboard_subtitle') }}
                    </p>
                </div>
                
                <!-- Refresh Prices Button -->
                <form action="{{ route('superadmin.refresh-prices') }}" method="POST" onsubmit="return confirm('This will refresh all cached prices. It may take a few minutes. Continue?');">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="font-semibold">Refresh All Prices</span>
                    </button>
                </form>
            </div>
            
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-900/30 border border-green-500/50 text-green-300 px-4 py-3 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-6 bg-red-900/30 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- System Overview -->
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-white mb-4">{{ __('messages.system_overview') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Total Users -->
                    <div class="bg-gradient-to-br from-blue-900/30 to-blue-800/20 border border-blue-500/30 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-300 text-sm mb-1">{{ __('messages.total_users') }}</p>
                                <p class="text-3xl font-bold text-white">{{ number_format($stats['total_users']) }}</p>
                            </div>
                            <div class="bg-blue-500/20 p-3 rounded-lg">
                                <i class="fa fa-users text-3xl text-blue-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Organizations -->
                    <div class="bg-gradient-to-br from-green-900/30 to-green-800/20 border border-green-500/30 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-300 text-sm mb-1">{{ __('messages.total_organizations') }}</p>
                                <p class="text-3xl font-bold text-white">{{ number_format($stats['total_organizations']) }}</p>
                            </div>
                            <div class="bg-green-500/20 p-3 rounded-lg">
                                <i class="fa fa-building text-3xl text-green-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Subscriptions -->
                    <div class="bg-gradient-to-br from-purple-900/30 to-purple-800/20 border border-purple-500/30 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-300 text-sm mb-1">{{ __('messages.active_subscriptions') }}</p>
                                <p class="text-3xl font-bold text-white">{{ number_format($activeSubscriptions) }}</p>
                            </div>
                            <div class="bg-purple-500/20 p-3 rounded-lg">
                                <i class="fa fa-star text-3xl text-purple-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Cards -->
                    <div class="bg-gradient-to-br from-yellow-900/30 to-yellow-800/20 border border-yellow-500/30 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-300 text-sm mb-1">{{ __('messages.total_cards') }}</p>
                                <p class="text-3xl font-bold text-white">{{ number_format($stats['total_cards']) }}</p>
                            </div>
                            <div class="bg-yellow-500/20 p-3 rounded-lg">
                                <i class="fa fa-id-card text-3xl text-yellow-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Expansions -->
                    <div class="bg-gradient-to-br from-red-900/30 to-red-800/20 border border-red-500/30 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-300 text-sm mb-1">{{ __('messages.total_expansions') }}</p>
                                <p class="text-3xl font-bold text-white">{{ number_format($stats['total_expansions']) }}</p>
                            </div>
                            <div class="bg-red-500/20 p-3 rounded-lg">
                                <i class="fa fa-layer-group text-3xl text-red-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Articles -->
                    <div class="bg-gradient-to-br from-pink-900/30 to-pink-800/20 border border-pink-500/30 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-pink-300 text-sm mb-1">{{ __('messages.total_articles') }}</p>
                                <p class="text-3xl font-bold text-white">{{ number_format($stats['total_articles']) }}</p>
                            </div>
                            <div class="bg-pink-500/20 p-3 rounded-lg">
                                <i class="fa fa-newspaper text-3xl text-pink-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Statistics -->
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-white mb-4">{{ __('messages.revenue_statistics') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white/5 border border-white/10 rounded-lg p-6">
                        <p class="text-gray-400 text-sm mb-2">{{ __('messages.last_30_days') }}</p>
                        <p class="text-2xl font-bold text-green-400">&euro;{{ number_format($revenueStats['last_30_days'], 2) }}</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-lg p-6">
                        <p class="text-gray-400 text-sm mb-2">{{ __('messages.this_month') }}</p>
                        <p class="text-2xl font-bold text-green-400">&euro;{{ number_format($revenueStats['last_month'], 2) }}</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-lg p-6">
                        <p class="text-gray-400 text-sm mb-2">{{ __('messages.this_year') }}</p>
                        <p class="text-2xl font-bold text-green-400">&euro;{{ number_format($revenueStats['this_year'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- User Engagement & Trial Statistics -->
            <div class="mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- User Engagement -->
                    <div>
                        <h3 class="font-semibold text-xl text-white mb-4">
                            <i class="fa fa-chart-line mr-2 text-blue-400"></i>
                            User Engagement (Last 30 Days)
                        </h3>
                        <div class="bg-white/5 border border-white/10 rounded-lg p-6 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">Active Users</span>
                                <span class="text-2xl font-bold text-blue-400">{{ number_format($engagementStats['active_users']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">New Registrations</span>
                                <span class="text-2xl font-bold text-green-400">{{ number_format($engagementStats['new_users_30d']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">Collections Created</span>
                                <span class="text-2xl font-bold text-purple-400">{{ number_format($engagementStats['collections_created_30d']) }}</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-white/10 pt-4">
                                <span class="text-gray-400">Total Collections</span>
                                <span class="text-xl font-bold text-white">{{ number_format($engagementStats['total_collections']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Trial Statistics -->
                    <div>
                        <h3 class="font-semibold text-xl text-white mb-4">
                            <i class="fa fa-clock mr-2 text-yellow-400"></i>
                            Trial Statistics
                        </h3>
                        <div class="bg-white/5 border border-white/10 rounded-lg p-6 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">Active Trials</span>
                                <span class="text-2xl font-bold text-yellow-400">{{ number_format($trialStats['active_trials']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">Expired Trials</span>
                                <span class="text-2xl font-bold text-gray-500">{{ number_format($trialStats['expired_trials']) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">Converted to Paid</span>
                                <span class="text-2xl font-bold text-green-400">{{ number_format($trialStats['converted_from_trial']) }}</span>
                            </div>
                            @if($trialStats['active_trials'] + $trialStats['expired_trials'] > 0)
                                <div class="flex justify-between items-center border-t border-white/10 pt-4">
                                    <span class="text-gray-400">Conversion Rate</span>
                                    <span class="text-xl font-bold text-green-400">
                                        {{ round(($trialStats['converted_from_trial'] / ($trialStats['active_trials'] + $trialStats['expired_trials'])) * 100, 1) }}%
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mapping Statistics - DEPRECATED: Old TCGCSV/RapidAPI mapping logic --}}
            @if(false)
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-white mb-4">{{ __('messages.data_mapping_status') }}</h3>
                
                <div class="bg-white/5 border border-white/10 rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-4">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('admin_mappings.tcgcsv_groups') }}</p>
                            <p class="text-xl font-bold text-white">{{ $mappingStats['tcgcsv_groups'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('admin_mappings.rapidapi_episodes') }}</p>
                            <p class="text-xl font-bold text-white">{{ $mappingStats['rapidapi_episodes'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('admin_mappings.mapped') }}</p>
                            <p class="text-xl font-bold text-green-400">{{ $mappingStats['mapped_groups'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm mb-1">{{ __('admin_mappings.unmapped') }}</p>
                            <p class="text-xl font-bold text-red-400">{{ $mappingStats['unmapped_groups'] }}</p>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-400 mb-2">
                            <span>{{ __('admin_mappings.mapping_progress') }}</span>
                            <span class="font-semibold">{{ $mappingStats['mapping_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-3">
                            <div class="bg-gradient-to-r from-green-500 to-green-400 h-3 rounded-full transition-all" 
                                 style="width: {{ $mappingStats['mapping_percentage'] }}%">
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <a href="{{ route('superadmin.rapidapi-mapping.index') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <i class="fa fa-link"></i>
                            <span>{{ __('admin_mappings.manage_mappings') }}</span>
                        </a>
                        
                        <a href="{{ route('superadmin.unmapped-collection.index') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition">
                            <i class="fa fa-exclamation-triangle"></i>
                            <span>Unmapped Cards</span>
                            <span class="ml-2 px-2 py-1 bg-white/20 rounded-full text-sm font-bold">{{ number_format($unmappedCardsStats['unmapped_count']) }}</span>
                        </a>
                        
                        <a href="{{ route('superadmin.cardmarket-comparison.index') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                            <i class="fa fa-balance-scale"></i>
                            <span>CardMarket Comparison</span>
                        </a>
                        
                        <a href="{{ route('superadmin.etl-console.index') }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition">
                            <i class="fas fa-sync-alt"></i>
                            <span>ETL Pipeline Console</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Games Breakdown -->
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-white mb-4">{{ __('messages.games_breakdown') }}</h3>
                
                <div class="bg-white/5 border border-white/10 rounded-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-white/5">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    {{ __('messages.game') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    {{ __('messages.cards') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    {{ __('messages.expansions') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    {{ __('messages.articles') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach($gameStats as $game)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($game['code'] === 'pokemon')
                                            <img src="/images/logos/logo_pokemon.png" alt="{{ $game['name'] }}" class="w-6 h-6 object-contain">
                                        @elseif($game['code'] === 'mtg')
                                            <span class="text-base font-bold text-white">MTG</span>
                                        @elseif($game['code'] === 'yugioh')
                                            <span class="text-base font-bold text-white">YGO</span>
                                        @endif
                                        <span class="text-white font-medium">{{ $game['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-white">{{ number_format($game['cards_count']) }}</td>
                                <td class="px-6 py-4 text-right text-white">{{ number_format($game['sets_count']) }}</td>
                                <td class="px-6 py-4 text-right text-white">{{ number_format($game['articles_count']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-white mb-4">{{ __('messages.recent_users') }}</h3>
                
                <div class="bg-white/5 border border-white/10 rounded-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-white/5">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    {{ __('messages.user') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    {{ __('messages.organization') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                    {{ __('messages.registered') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach($recentUsers as $user)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-white font-medium">{{ $user->name }}</p>
                                        <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-300">
                                    {{ $user->organization ? $user->organization->name : '-' }}
                                </td>
                                <td class="px-6 py-4 text-gray-300">
                                    {{ $user->created_at->diffForHumans() }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-white mb-4">{{ __('messages.quick_actions') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('superadmin.organizations.index') }}" 
                       class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg p-6 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-green-500/20 p-3 rounded-lg group-hover:bg-green-500/30 transition">
                                <i class="fa fa-building text-2xl text-green-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold">{{ __('messages.organizations') }}</h4>
                                <p class="text-gray-400 text-sm">{{ __('messages.manage_organizations') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('superadmin.plans.index') }}" 
                       class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg p-6 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-purple-500/20 p-3 rounded-lg group-hover:bg-purple-500/30 transition">
                                <i class="fa fa-tags text-2xl text-purple-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold">{{ __('messages.pricing_plans') }}</h4>
                                <p class="text-gray-400 text-sm">{{ __('messages.manage_plans') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('admin.articles.index') }}" 
                       class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg p-6 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-blue-500/20 p-3 rounded-lg group-hover:bg-blue-500/30 transition">
                                <i class="fa fa-newspaper text-2xl text-blue-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold">{{ __('messages.articles') }}</h4>
                                <p class="text-gray-400 text-sm">{{ __('messages.manage_articles') }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('superadmin.rapidapi-mapping.index') }}" 
                       class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg p-6 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-yellow-500/20 p-3 rounded-lg group-hover:bg-yellow-500/30 transition">
                                <i class="fa fa-link text-2xl text-yellow-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold">{{ __('admin_mappings.title') }}</h4>
                                <p class="text-gray-400 text-sm">{{ __('admin_mappings.subtitle') }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
