<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Stats Overview --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                {{-- TCGCSV Stats --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">TCGCSV</h3>
                            @if($stats['tcgcsv']['last_run'])
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @endif
                        </div>
                        <div class="text-3xl font-bold text-gray-900 mb-1">
                            {{ $stats['tcgcsv']['total_runs'] }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $stats['tcgcsv']['success_rate'] }}% success rate
                        </div>
                        @if($stats['tcgcsv']['last_run'])
                            <div class="text-xs text-gray-500 mt-2">
                                Last: {{ \Carbon\Carbon::parse($stats['tcgcsv']['last_run']->created_at)->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Cardmarket Stats --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">Cardmarket</h3>
                            @if($stats['cardmarket']['last_run'])
                                <span class="px-2 py-1 text-xs rounded-full {{ $stats['cardmarket']['last_run']->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($stats['cardmarket']['last_run']->status ?? 'unknown') }}
                                </span>
                            @endif
                        </div>
                        <div class="text-3xl font-bold text-gray-900 mb-1">
                            {{ $stats['cardmarket']['total_runs'] }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $stats['cardmarket']['success_rate'] }}% success rate
                        </div>
                        @if($stats['cardmarket']['last_run'])
                            <div class="text-xs text-gray-500 mt-2">
                                Last: {{ \Carbon\Carbon::parse($stats['cardmarket']['last_run']->created_at)->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- RapidAPI Stats --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">RapidAPI</h3>
                            @if($stats['rapidapi']['last_run'])
                                <span class="px-2 py-1 text-xs rounded-full {{ $stats['rapidapi']['last_run']->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($stats['rapidapi']['last_run']->status ?? 'unknown') }}
                                </span>
                            @endif
                        </div>
                        <div class="text-3xl font-bold text-gray-900 mb-1">
                            {{ $stats['rapidapi']['total_runs'] }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $stats['rapidapi']['success_rate'] }}% success rate
                        </div>
                        @if($stats['rapidapi']['last_run'])
                            <div class="text-xs text-gray-500 mt-2">
                                Last: {{ \Carbon\Carbon::parse($stats['rapidapi']['last_run']->created_at)->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- TCGdex Stats --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">TCGdex</h3>
                            @if($stats['tcgdex']['last_run'])
                                <span class="px-2 py-1 text-xs rounded-full {{ $stats['tcgdex']['last_run']->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($stats['tcgdex']['last_run']->status ?? 'unknown') }}
                                </span>
                            @endif
                        </div>
                        <div class="text-3xl font-bold text-gray-900 mb-1">
                            {{ $stats['tcgdex']['total_runs'] }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $stats['tcgdex']['success_rate'] }}% success rate
                        </div>
                        @if($stats['tcgdex']['last_run'])
                            <div class="text-xs text-gray-500 mt-2">
                                Last: {{ \Carbon\Carbon::parse($stats['tcgdex']['last_run']->created_at)->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent Import Runs --}}
            <div class="space-y-6">
                
                {{-- TCGCSV Runs --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">TCGCSV Import Logs</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mode</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Groups</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Products</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prices</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($tcgcsvRuns as $run)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($run->created_at)->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $run->type ?? 'all' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if($run->groups_new || $run->groups_updated)
                                                    <span class="text-green-600">+{{ $run->groups_new ?? 0 }}</span>
                                                    <span class="text-blue-600">~{{ $run->groups_updated ?? 0 }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if($run->products_new || $run->products_updated)
                                                    <span class="text-green-600">+{{ $run->products_new ?? 0 }}</span>
                                                    <span class="text-blue-600">~{{ $run->products_updated ?? 0 }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if($run->prices_new || $run->prices_updated)
                                                    <span class="text-green-600">+{{ $run->prices_new ?? 0 }}</span>
                                                    <span class="text-blue-600">~{{ $run->prices_updated ?? 0 }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if(isset($run->started_at) && isset($run->completed_at) && $run->completed_at)
                                                    {{ \Carbon\Carbon::parse($run->started_at)->diffInSeconds(\Carbon\Carbon::parse($run->completed_at)) }}s
                                                @elseif(isset($run->duration_ms))
                                                    {{ round($run->duration_ms / 1000) }}s
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($run->status === 'failed')
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                        Failed
                                                    </span>
                                                @elseif($run->status === 'completed')
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                        Success
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                        {{ ucfirst($run->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                                No import runs yet
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Cardmarket Runs --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cardmarket Import Runs</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rows Read</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rows Upserted</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($cardmarketRuns as $run)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($run->created_at)->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ ucfirst($run->import_type ?? '-') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ number_format($run->rows_read ?? 0) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ number_format($run->rows_upserted ?? 0) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if(isset($run->started_at) && isset($run->finished_at) && $run->finished_at)
                                                    {{ \Carbon\Carbon::parse($run->started_at)->diffInSeconds(\Carbon\Carbon::parse($run->finished_at)) }}s
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $run->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($run->status ?? 'unknown') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                                No import runs yet
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- RapidAPI Runs --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">RapidAPI Sync Logs</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Game</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cards Fetched</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($rapidapiRuns as $run)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($run->created_at)->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $run->game ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $run->cards_fetched ?? 0 }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if(isset($run->started_at) && isset($run->finished_at) && $run->finished_at)
                                                    {{ \Carbon\Carbon::parse($run->started_at)->diffInSeconds(\Carbon\Carbon::parse($run->finished_at)) }}s
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $run->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($run->status ?? 'unknown') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                                No sync runs yet
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TCGdex Runs --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">TCGdex Import Runs</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sets</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cards</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($tcgdexRuns as $run)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($run->created_at)->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                <span class="text-green-600">+{{ $run->sets_new ?? 0 }}</span>
                                                <span class="text-blue-600">~{{ $run->sets_updated ?? 0 }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                <span class="text-green-600">+{{ $run->cards_new ?? 0 }}</span>
                                                <span class="text-blue-600">~{{ $run->cards_updated ?? 0 }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if(isset($run->started_at) && isset($run->finished_at) && $run->finished_at)
                                                    {{ \Carbon\Carbon::parse($run->started_at)->diffInSeconds(\Carbon\Carbon::parse($run->finished_at)) }}s
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $run->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($run->status ?? 'unknown') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                                No import runs yet
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
