<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PipelineRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class EtlConsoleController extends Controller
{
    // Definizione degli step della pipeline
    private array $steps = [
        [
            'id' => 'cardmarket-download-products',
            'name' => 'Cardmarket Download Products',
            'command' => 'cardmarket:download --products',
            'description' => 'Download catalogo prodotti da Cardmarket',
            'estimated_duration' => '2-3 min',
            'frequency' => '1x/giorno',
            'task_name' => 'cardmarket:download:products'
        ],
        [
            'id' => 'cardmarket-download-prices',
            'name' => 'Cardmarket Download Prices',
            'command' => 'cardmarket:download --prices',
            'description' => 'Download prezzi da Cardmarket',
            'estimated_duration' => '1-2 min',
            'frequency' => '1x/giorno',
            'task_name' => 'cardmarket:download:prices'
        ],
        [
            'id' => 'cardmarket-import-products',
            'name' => 'Cardmarket Import Products',
            'command' => 'cardmarket:import --products',
            'description' => 'Import prodotti nel database',
            'estimated_duration' => '1-2 min',
            'frequency' => '1x/giorno',
            'task_name' => 'cardmarket:import:products'
        ],
        [
            'id' => 'cardmarket-import-prices',
            'name' => 'Cardmarket Import Prices',
            'command' => 'cardmarket:import --prices',
            'description' => 'Import prezzi nel database',
            'estimated_duration' => '30s-1min',
            'frequency' => '1x/giorno',
            'task_name' => 'cardmarket:import:prices'
        ],
        [
            'id' => 'tcgcsv-import',
            'name' => 'TCGCSV Import Pokemon',
            'command' => 'tcgcsv:import-pokemon',
            'description' => 'Import dati TCG da tcgcsv.com',
            'estimated_duration' => '5-10 min',
            'frequency' => '1x/giorno',
            'task_name' => 'tcgcsv:import-pokemon'
        ],
        [
            'id' => 'rapidapi-import-episodes',
            'name' => 'RapidAPI Import Episodes',
            'command' => 'rapidapi:import-episodes pokemon',
            'description' => 'Import lista episodi Pokemon',
            'estimated_duration' => '10-30s',
            'frequency' => '1x/giorno',
            'task_name' => 'rapidapi:import-episodes'
        ],
        [
            'id' => 'rapidapi-sync-cards',
            'name' => 'RapidAPI Sync Cards',
            'command' => 'rapidapi:sync-cards pokemon',
            'description' => 'Sync carte episodi con prezzi (rate limit)',
            'estimated_duration' => '8-10 min',
            'frequency' => '1-4x/giorno',
            'task_name' => 'rapidapi:sync-cards'
        ],
        [
            'id' => 'cards-map',
            'name' => 'RapidAPI Cards Mapping',
            'command' => 'cards:map',
            'description' => 'Mapping RapidAPI → TCGCSV',
            'estimated_duration' => '10-30s',
            'frequency' => 'Dopo sync',
            'task_name' => 'cards:map'
        ],
        [
            'id' => 'cardmarket-match',
            'name' => 'Cardmarket Match Metacards',
            'command' => 'cardmarket:match-metacards --auto-confirm',
            'description' => 'Direct + Fuzzy matching',
            'estimated_duration' => '2-3 min',
            'frequency' => '1x/giorno',
            'task_name' => 'cardmarket:match-metacards'
        ],
        [
            'id' => 'tcgdex-import',
            'name' => 'TCGdex Import',
            'command' => 'tcgdx:import',
            'description' => 'Import set e carte da TCGdex API',
            'estimated_duration' => '1-2 min',
            'frequency' => '1-2x/giorno',
            'task_name' => 'tcgdx:import'
        ],
        [
            'id' => 'rapidapi-map-episodes',
            'name' => 'RapidAPI Episodes Mapping',
            'command' => 'rapidapi:map-episodes',
            'description' => 'Mapping episodi → gruppi TCGCSV',
            'estimated_duration' => '10-30s',
            'frequency' => 'Dopo import episodes',
            'task_name' => 'rapidapi:map-episodes'
        ],
        [
            'id' => 'tcgdex-map-tcgcsv',
            'name' => 'TCGdex to TCGCSV Mapping',
            'command' => 'tcgdex:map-to-tcgcsv',
            'description' => 'Mapping TCGdex → TCGCSV',
            'estimated_duration' => '5-15s',
            'frequency' => 'Dopo import TCGdex',
            'task_name' => 'tcgdex:map-to-tcgcsv'
        ],
        [
            'id' => 'tcgdex-map-cardmarket',
            'name' => 'TCGdex CardMarket IDs',
            'command' => 'tcgdex:map-cardmarket',
            'description' => 'Estrai CardMarket product IDs',
            'estimated_duration' => '5-15s',
            'frequency' => 'Dopo import TCGdex',
            'task_name' => 'tcgdex:map-cardmarket'
        ],
        [
            'id' => 'tcgcsv-enrich',
            'name' => 'TCGCSV Enrichment',
            'command' => 'tcgcsv:enrich --all',
            'description' => 'Arricchisci con immagini HD, prezzi, link',
            'estimated_duration' => '2-5 min',
            'frequency' => '1x/giorno',
            'task_name' => 'tcgcsv:enrich'
        ],
        [
            'id' => 'cardmarket-sync-prices',
            'name' => 'Cardmarket Sync Prices',
            'command' => 'cardmarket:sync-prices --force',
            'description' => 'Sync prezzi trend Cardmarket → TCGCSV',
            'estimated_duration' => '1-2 min',
            'frequency' => '1x/giorno',
            'task_name' => 'cardmarket:sync-prices'
        ],
    ];

    public function index()
    {
        // Recupera l'ultima run per ogni task
        $lastRuns = PipelineRun::selectRaw('task_name, MAX(started_at) as last_run')
            ->groupBy('task_name')
            ->get()
            ->keyBy('task_name');

        // Arricchisci gli step con le info dell'ultima run
        $stepsWithStatus = collect($this->steps)->map(function ($step) use ($lastRuns) {
            $taskName = $step['task_name'];
            
            if ($lastRuns->has($taskName)) {
                $lastRun = PipelineRun::where('task_name', $taskName)
                    ->orderBy('started_at', 'desc')
                    ->first();
                
                $step['last_run'] = $lastRun;
            } else {
                $step['last_run'] = null;
            }
            
            return $step;
        });

        // Statistiche generali
        $stats = [
            'total_runs' => PipelineRun::count(),
            'last_24h' => PipelineRun::where('started_at', '>=', now()->subDay())->count(),
            'failed_last_24h' => PipelineRun::where('started_at', '>=', now()->subDay())
                ->where('status', 'failed')
                ->count(),
            'running' => PipelineRun::where('status', 'running')->count(),
        ];

        // Ultime 10 run
        $recentRuns = PipelineRun::orderBy('started_at', 'desc')
            ->limit(10)
            ->get();

        return view('superadmin.etl-console.index', compact('stepsWithStatus', 'stats', 'recentRuns'));
    }

    public function runTask(Request $request)
    {
        $stepId = $request->input('step_id');
        
        // Trova lo step
        $step = collect($this->steps)->firstWhere('id', $stepId);
        
        if (!$step) {
            return response()->json(['error' => 'Step non trovato'], 404);
        }

        // Esegui il comando in background
        $command = $step['command'];
        
        try {
            // Lancia il comando Artisan in background usando nohup
            $artisanPath = base_path('artisan');
            $commandParts = explode(' ', $command);
            $commandName = array_shift($commandParts);
            $commandArgs = implode(' ', $commandParts);
            
            $logPath = storage_path('logs/etl-' . $step['id'] . '.log');
            
            $fullCommand = sprintf(
                'nohup php %s %s %s >> %s 2>&1 &',
                escapeshellarg($artisanPath),
                escapeshellarg($commandName),
                $commandArgs,
                escapeshellarg($logPath)
            );
            
            exec($fullCommand);

            return response()->json([
                'success' => true,
                'message' => "Task '{$step['name']}' avviato in background",
                'step_id' => $stepId,
                'task_name' => $step['task_name'],
                'log_path' => $logPath
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Errore durante l\'avvio del task: ' . $e->getMessage()
            ], 500);
        }
    }

    public function runAll()
    {
        try {
            // Lancia lo script bash della pipeline completa
            $scriptPath = base_path('simulate-etl-pipeline.sh');
            
            if (!file_exists($scriptPath)) {
                return response()->json([
                    'error' => 'Script pipeline non trovato'
                ], 404);
            }

            $fullCommand = sprintf(
                'bash %s > /dev/null 2>&1 &',
                escapeshellarg($scriptPath)
            );
            
            exec($fullCommand);

            return response()->json([
                'success' => true,
                'message' => 'Pipeline completa avviata in background',
                'estimated_duration' => '20-30 minuti'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Errore durante l\'avvio della pipeline: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatus(Request $request)
    {
        $taskName = $request->input('task_name');
        
        // Recupera l'ultima run del task
        $lastRun = PipelineRun::where('task_name', $taskName)
            ->orderBy('started_at', 'desc')
            ->first();

        if (!$lastRun) {
            return response()->json([
                'status' => 'never_run',
                'message' => 'Task mai eseguito'
            ]);
        }

        return response()->json([
            'status' => $lastRun->status,
            'started_at' => $lastRun->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $lastRun->completed_at?->format('Y-m-d H:i:s'),
            'duration' => $lastRun->duration,
            'rows_processed' => $lastRun->rows_processed,
            'errors_count' => $lastRun->errors_count,
            'error_message' => $lastRun->error_message,
        ]);
    }

    public function getLogs(Request $request)
    {
        $limit = $request->input('limit', 20);
        
        $runs = PipelineRun::orderBy('started_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($run) {
                return [
                    'id' => $run->id,
                    'task_name' => $run->task_name,
                    'status' => $run->status,
                    'started_at' => $run->started_at?->format('Y-m-d H:i:s'),
                    'duration' => $run->duration,
                    'rows_processed' => $run->rows_processed,
                    'errors_count' => $run->errors_count,
                ];
            });

        return response()->json($runs);
    }
}
