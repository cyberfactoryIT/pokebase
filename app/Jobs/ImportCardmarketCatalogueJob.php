<?php

namespace App\Jobs;

use App\Models\CardmarketImportRun;
use App\Services\Cardmarket\CardmarketImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportCardmarketCatalogueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;
    public int $tries;
    
    protected string $csvPath;
    protected ?string $runUuid;

    /**
     * Create a new job instance.
     */
    public function __construct(string $csvPath, ?string $runUuid = null)
    {
        $this->csvPath = $csvPath;
        $this->runUuid = $runUuid;
        $this->timeout = config('cardmarket.queue.timeout', 3600);
        $this->tries = config('cardmarket.queue.tries', 3);
        $this->onQueue(config('cardmarket.queue.name'));
    }

    /**
     * Execute the job.
     */
    public function handle(CardmarketImporter $importer): void
    {
        $runUuid = $this->runUuid ?? (string) Str::uuid();
        $logChannel = config('cardmarket.logging.channel', 'cardmarket');
        
        Log::channel($logChannel)->info('Catalogue import job started', [
            'run_uuid' => $runUuid,
            'csv_path' => $this->csvPath,
        ]);

        // Create or find import run
        $run = CardmarketImportRun::firstOrCreate(
            ['run_uuid' => $runUuid],
            [
                'type' => 'catalogue',
                'status' => 'running',
            ]
        );

        try {
            $result = $importer->importCatalogue($this->csvPath, $run);
            
            if ($result['success']) {
                $run->markSuccess();
                
                Log::channel($logChannel)->info('Catalogue import job completed', [
                    'run_uuid' => $runUuid,
                    'rows_read' => $result['rows_read'],
                    'rows_upserted' => $result['rows_upserted'],
                ]);
            } else {
                throw new \Exception($result['message']);
            }

        } catch (\Exception $e) {
            $run->markFailed($e->getMessage());
            
            Log::channel($logChannel)->error('Catalogue import job failed', [
                'run_uuid' => $runUuid,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
