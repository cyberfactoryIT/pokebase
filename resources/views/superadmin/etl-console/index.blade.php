@extends('layouts.app')

@section('title', 'ETL Pipeline Console')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">🔧 ETL Pipeline Console</h1>
        <p class="text-gray-600">Monitora e gestisci l'importazione dati da Cardmarket, TCGCSV, RapidAPI e TCGdex</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Runs</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_runs'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Last 24h</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['last_24h'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Failed (24h)</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['failed_last_24h'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Running Now</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['running'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Pipeline Actions</h2>
                <p class="text-sm text-gray-600">Esegui l'intera pipeline o singoli step</p>
            </div>
            <button onclick="runFullPipeline()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2 transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Run Full Pipeline</span>
            </button>
        </div>
    </div>

    <!-- Pipeline Steps -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-4 py-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Pipeline Steps</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($stepsWithStatus as $step)
            <div class="p-4 hover:bg-gray-50 transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <h3 class="text-base font-semibold text-gray-800">{{ $step['name'] }}</h3>
                            @if($step['last_run'])
                                @if($step['last_run']->status === 'completed')
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">✓ Completed</span>
                                @elseif($step['last_run']->status === 'running')
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full animate-pulse">⏳ Running</span>
                                @elseif($step['last_run']->status === 'failed')
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">✗ Failed</span>
                                @endif
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Never Run</span>
                            @endif
                            <span class="px-2 py-1 text-xs bg-blue-50 text-blue-700 rounded">{{ $step['frequency'] }}</span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-2">{{ $step['description'] }}</p>
                        
                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                            <span>⏱️ {{ $step['estimated_duration'] }}</span>
                            @if($step['last_run'])
                                <span>Last run: {{ $step['last_run']->started_at->diffForHumans() }}</span>
                                @if($step['last_run']->duration)
                                    <span>Duration: {{ $step['last_run']->duration }}</span>
                                @endif
                                @if($step['last_run']->rows_processed)
                                    <span>Rows: {{ number_format($step['last_run']->rows_processed) }}</span>
                                @endif
                                @if($step['last_run']->errors_count > 0)
                                    <span class="text-red-600">Errors: {{ $step['last_run']->errors_count }}</span>
                                @endif
                            @endif
                        </div>

                        @if($step['last_run'] && $step['last_run']->error_message)
                        <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700">
                            {{ $step['last_run']->error_message }}
                        </div>
                        @endif
                    </div>
                    
                    <button 
                        onclick="runTask('{{ $step['id'] }}', '{{ $step['task_name'] }}')" 
                        class="ml-4 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition flex items-center space-x-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        </svg>
                        <span>Run</span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Runs Log -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Recent Activity</h2>
            <button onclick="refreshLogs()" class="text-sm text-blue-600 hover:text-blue-700 flex items-center space-x-1">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Refresh</span>
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="logs-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rows</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Errors</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentRuns as $run)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $run->task_name }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($run->status === 'completed')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Completed</span>
                            @elseif($run->status === 'running')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Running</span>
                            @elseif($run->status === 'failed')
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Failed</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $run->started_at?->format('Y-m-d H:i:s') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $run->duration ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $run->rows_processed ? number_format($run->rows_processed) : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $run->errors_count ?? 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed bottom-4 right-4 bg-gray-900 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <p id="toast-message"></p>
</div>

<script>
// Run single task
async function runTask(stepId, taskName) {
    console.log('runTask called:', stepId, taskName);
    if (!confirm('Sei sicuro di voler eseguire questo task?')) return;
    
    showToast('Avvio task in corso...', 'info');
    
    try {
        const response = await fetch('/superadmin/etl-console/run-task', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ step_id: stepId })
        });
        
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            showToast(data.message, 'success');
            // Refresh page after 2 seconds
            setTimeout(() => location.reload(), 2000);
        } else {
            showToast(data.error || 'Errore durante l\'avvio del task', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Errore di rete: ' + error.message, 'error');
    }
}

// Run full pipeline
async function runFullPipeline() {
    if (!confirm('Sei sicuro di voler eseguire l\'intera pipeline? Durata stimata: 20-30 minuti')) return;
    
    showToast('Avvio pipeline completa in corso...', 'info');
    
    try {
        const response = await fetch('/superadmin/etl-console/run-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            // Refresh page after 3 seconds
            setTimeout(() => location.reload(), 3000);
        } else {
            showToast(data.error || 'Errore durante l\'avvio della pipeline', 'error');
        }
    } catch (error) {
        showToast('Errore di rete: ' + error.message, 'error');
    }
}

// Refresh logs
async function refreshLogs() {
    try {
        const response = await fetch('/superadmin/etl-console/logs?limit=10');
        const logs = await response.json();
        
        // Update table
        const tbody = document.querySelector('#logs-table tbody');
        tbody.innerHTML = logs.map(log => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">${log.task_name}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs font-medium bg-${log.status === 'completed' ? 'green' : log.status === 'running' ? 'yellow' : 'red'}-100 text-${log.status === 'completed' ? 'green' : log.status === 'running' ? 'yellow' : 'red'}-800 rounded-full">
                        ${log.status.charAt(0).toUpperCase() + log.status.slice(1)}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">${log.started_at}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${log.duration || '-'}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${log.rows_processed ? log.rows_processed.toLocaleString() : '-'}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${log.errors_count || 0}</td>
            </tr>
        `).join('');
        
        showToast('Logs aggiornati', 'success');
    } catch (error) {
        showToast('Errore durante il refresh dei logs', 'error');
    }
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    
    toastMessage.textContent = message;
    toast.classList.remove('hidden', 'bg-gray-900', 'bg-green-600', 'bg-red-600', 'bg-blue-600');
    
    if (type === 'success') {
        toast.classList.add('bg-green-600');
    } else if (type === 'error') {
        toast.classList.add('bg-red-600');
    } else if (type === 'info') {
        toast.classList.add('bg-blue-600');
    } else {
        toast.classList.add('bg-gray-900');
    }
    
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 5000);
}

// Auto-refresh logs every 30 seconds
setInterval(refreshLogs, 30000);
</script>

@endsection
