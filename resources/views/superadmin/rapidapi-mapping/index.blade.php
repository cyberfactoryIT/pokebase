@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('admin_mappings.title') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('admin_mappings.subtitle') }}</p>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">{{ __('admin_mappings.total_groups') }}</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($statistics['total_groups']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">{{ __('admin_mappings.mapped_groups') }}</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($statistics['mapped_groups']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">{{ __('admin_mappings.unmapped_groups') }}</div>
                <div class="text-2xl font-bold text-orange-600">{{ number_format($statistics['unmapped_groups']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">{{ __('admin_mappings.available_expansions') }}</div>
                <div class="text-2xl font-bold text-blue-600">{{ number_format($statistics['available_episodes']) }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ __('admin_mappings.mapping_progress') }}: {{ $statistics['mapping_percentage'] }}%</div>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('superadmin.rapidapi-mapping.index') }}" class="flex flex-col sm:flex-row gap-4">
                {{-- Filter --}}
                <div class="flex-shrink-0">
                    <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin_mappings.filters.label') }}</label>
                    <select name="filter" id="filter" onchange="this.form.submit()" class="block w-full sm:w-48 rounded-md border-gray-300 bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>{{ __('admin_mappings.filters.all') }}</option>
                        <option value="mapped" {{ $filter === 'mapped' ? 'selected' : '' }}>{{ __('admin_mappings.filters.mapped') }}</option>
                        <option value="unmapped" {{ $filter === 'unmapped' ? 'selected' : '' }}>{{ __('admin_mappings.filters.unmapped') }}</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="flex-grow">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin_mappings.search.button') }}</label>
                    <div class="flex gap-2">
                        <input type="text" name="search" id="search" value="{{ $search }}" placeholder="{{ __('admin_mappings.search.placeholder') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ __('admin_mappings.search.button') }}
                        </button>
                        @if($search || $filter !== 'all')
                            <a href="{{ route('superadmin.rapidapi-mapping.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($groups->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin_mappings.table.group') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin_mappings.table.abbreviation') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin_mappings.table.published_date') }}</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin_mappings.table.cards_count') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin_mappings.table.status') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin_mappings.table.rapidapi') }}</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin_mappings.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($groups as $group)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $group->name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $group->group_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $group->abbreviation ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $group->published_on ? $group->published_on->format('Y-m-d') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center">
                                        <span class="font-semibold">{{ number_format($group->cards_count) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($group->rapidapiEpisodes->count() > 0 || $group->rapidapi_episode_id)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('admin_mappings.table.mapped') }}
                                                @if($group->rapidapiEpisodes->count() > 1)
                                                    <span class="ml-1">({{ $group->rapidapiEpisodes->count() }})</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ __('admin_mappings.table.unmapped') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($group->rapidapiEpisodes->count() > 0)
                                            {{-- Show all mapped episodes using many-to-many relationship --}}
                                            @foreach($group->rapidapiEpisodes as $episode)
                                                <div class="mb-2 {{ !$loop->last ? 'pb-2 border-b border-gray-200' : '' }}">
                                                    <div class="text-sm font-medium text-gray-900">{{ $episode->name }}</div>
                                                    <div class="text-xs text-gray-500">
                                                        ID: {{ $episode->episode_id }}
                                                        @if($episode->code)
                                                            | Code: {{ $episode->code }}
                                                        @endif
                                                        @if($episode->game)
                                                            | {{ ucfirst($episode->game) }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @elseif($group->rapidapi_episode_id && $group->rapidapiEpisode)
                                            {{-- Fallback to old single relationship for backward compatibility --}}
                                            <div class="text-sm text-gray-900">{{ $group->rapidapiEpisode->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                ID: {{ $group->rapidapiEpisode->episode_id }}
                                                @if($group->rapidapiEpisode->code)
                                                    | Code: {{ $group->rapidapiEpisode->code }}
                                                @endif
                                                @if($group->rapidapiEpisode->game)
                                                    | {{ ucfirst($group->rapidapiEpisode->game) }}
                                                @endif
                                            </div>
                                        @else
                                            @php
                                                $suggestion = $suggestions[$group->id] ?? null;
                                                $availableForGroup = app(\App\Services\RapidapiGroupMappingService::class)->listAvailableRapidapiExpansions($group);
                                            @endphp
                                            
                                            @if($suggestion)
                                                <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="text-sm font-medium text-blue-800">{{ __('admin_mappings.actions.suggested_match') }}</span>
                                                    </div>
                                                    <div class="text-sm text-blue-900 font-medium">{{ $suggestion->name }}</div>
                                                    <div class="text-xs text-blue-700">
                                                        @if($suggestion->code) {{ $suggestion->code }} | @endif
                                                        {{ ucfirst($suggestion->game) }} | {{ $suggestion->released_at->format('Y-m-d') }}
                                                    </div>
                                                    <form method="POST" action="{{ route('superadmin.rapidapi-mapping.map', $group) }}" class="mt-2">
                                                        @csrf
                                                        <input type="hidden" name="rapidapi_episode_id" value="{{ $suggestion->episode_id }}">
                                                        <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                            {{ __('admin_mappings.actions.map_suggested') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                            
                                            <form method="POST" action="{{ route('superadmin.rapidapi-mapping.map', $group) }}" class="flex flex-col gap-2">
                                                @csrf
                                                <select name="rapidapi_episode_id" class="block w-full text-sm rounded-md border-gray-300 bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                                    <option value="">{{ __('admin_mappings.actions.select_expansion') }}</option>
                                                    @foreach($availableForGroup as $expansion)
                                                        @php
                                                            $isSuggestion = $suggestion && $expansion->episode_id === $suggestion->episode_id;
                                                            $dateMatch = $group->published_on && $expansion->released_at && 
                                                                        $group->published_on->format('Y-m-d') === $expansion->released_at->format('Y-m-d');
                                                        @endphp
                                                        <option value="{{ $expansion->episode_id }}" 
                                                                @if($isSuggestion) selected @endif
                                                                class="@if($dateMatch) font-semibold @endif">
                                                            @if($dateMatch)⭐ @endif{{ $expansion->name }}
                                                            @if($expansion->code) ({{ $expansion->code }}) @endif
                                                            - {{ ucfirst($expansion->game) }}
                                                            @if($expansion->released_at) - {{ $expansion->released_at->format('Y-m-d') }} @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="px-3 py-1.5 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                    {{ __('admin_mappings.actions.map') }}
                                                </button>
                                            </form>
                                            @if($availableForGroup->isEmpty())
                                                <div class="text-sm text-gray-500 italic">{{ __('admin_mappings.messages.no_available_expansions') }}</div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($group->rapidapi_episode_id)
                                            <form method="POST" action="{{ route('superadmin.rapidapi-mapping.unmap', $group) }}" onsubmit="return confirm('{{ __('admin_mappings.actions.confirm_unmap') }}')">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    {{ __('admin_mappings.actions.unmap') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $groups->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center text-gray-500">
                    {{ __('admin_mappings.messages.no_results') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
