@extends('layouts.app')

@section('content')
<div class="bg-black min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl">
            <!-- Header -->
            <div class="border-b border-white/10 px-6 py-4">
                <h1 class="text-3xl font-bold text-white">TCGdex Import Runs</h1>
                <p class="mt-1 text-sm text-gray-300">Last 20 import runs</p>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10">
                    <thead class="bg-black/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Scope</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Started</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Finished</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Stats</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Error</th>
                        </tr>
                    </thead>
                    <tbody class="bg-black/20 divide-y divide-white/10">
                        @forelse($runs as $run)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $run->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($run->status === 'success')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400">Success</span>
                                @elseif($run->status === 'failed')
                                <span class="px-2 py-1 text-xs rounded-full bg-red-500/20 text-red-400">Failed</span>
                                @else
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-400">Running</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $run->scope ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ $run->started_at ? $run->started_at->format('Y-m-d H:i:s') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ $run->finished_at ? $run->finished_at->format('Y-m-d H:i:s') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                @if($run->started_at && $run->finished_at)
                                {{ $run->started_at->diffForHumans($run->finished_at, true) }}
                                @else
                                N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">
                                @if($run->stats)
                                <div class="space-y-1">
                                    @if(isset($run->stats['sets_imported']))
                                    <div>Sets: {{ $run->stats['sets_imported'] }}/{{ $run->stats['sets_total'] ?? 0 }}</div>
                                    @endif
                                    @if(isset($run->stats['cards_total']))
                                    <div>Cards: {{ $run->stats['cards_total'] }}</div>
                                    @endif
                                    @if(!empty($run->stats['failed_sets']))
                                    <div class="text-red-400">Failed: {{ count($run->stats['failed_sets']) }}</div>
                                    @endif
                                </div>
                                @else
                                N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-red-400 max-w-xs truncate">
                                {{ $run->error_message ? Str::limit($run->error_message, 50) : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                No import runs found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="border-t border-white/10 px-6 py-4 text-sm text-gray-400">
                Run import: <code class="bg-black/50 px-2 py-1 rounded text-blue-400">php artisan tcgdx:import</code>
            </div>
        </div>
    </div>
</div>
@endsection
