<?php

namespace App\Jobs;

use App\Models\CardmarketImportRun;
use App\Services\Cardmarket\CardmarketDownloader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DownloadCardmarketFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;
    public int $tries;
    
    protected ?string $asOfDate;
    protected bool $forceDownload;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $asOfDate = null, bool $forceDownload = false)
    {
        $this->asOfDate = $asOfDate;
        $this->forceDownload = $forceDownload;
        $this->timeout = config('cardmarket.queue.timeout', 3600);
        $this->tries = config('cardmarket.queue.tries', 3);
        $this->onQueue(config('cardmarket.queue.name'));
    }

    /**
     * Execute the job.
     */
    public function handle(CardmarketDownloader $downloader): void
    {
        $runUuid = (string) Str::uuid();
        $logChannel = config('cardmarket.logging.channel', 'cardmarket');
        
        Log::channel($logChannel)->info('Download job started', [
            'run_uuid' => $runUuid,
            'as_of_date' => $this->asOfDate,
            'force_download' => $this->forceDownload,
        ]);

        try {
            // Download catalogue
            $catalogueResult = $downloader->downloadCatalogue($runUuid, $this->forceDownload);
            
            if (!$catalogueResult['success']) {
                throw new \Exception("Catalogue download failed: {$catalogueResult['message']}");
            }

            // Download price guide
            $priceGuideResult = $downloader->downloadPriceGuide($runUuid, $this->forceDownload);
            
            if (!$priceGuideResult['success']) {
                throw new \Exception("Price guide download failed: {$priceGuideResult['message']}");
            }

            Log::channel($logChannel)->info('Download job completed', [
                'run_uuid' => $runUuid,
                'catalogue_path' => $catalogueResult['path'],
                'priceguide_path' => $priceGuideResult['path'],
            ]);

            // Chain import jobs
            ImportCardmarketCatalogueJob::dispatch($catalogueResult['path'], $runUuid)
                ->onQueue(config('cardmarket.queue.name'));
            
            ImportCardmarketPriceGuideJob::dispatch($priceGuideResult['path'], $this->asOfDate, $runUuid)
                ->onQueue(config('cardmarket.queue.name'));

        } catch (\Exception $e) {
            Log::channel($logChannel)->error('Download job failed', [
                'run_uuid' => $runUuid,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
