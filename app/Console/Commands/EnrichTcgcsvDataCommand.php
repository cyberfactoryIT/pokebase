<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\PipelineRun;

class EnrichTcgcsvDataCommand extends Command
{
    protected $signature = 'tcgcsv:enrich 
                            {--images : Enrich with HD images from RapidAPI}
                            {--prices : Enrich with Cardmarket prices}
                            {--links : Enrich with external links (TCGO, Cardmarket)}
                            {--details : Enrich with card details (HP, artist, supertype, rarity)}
                            {--all : Enrich with all available data}';

    protected $description = 'Enrich TCGCSV data with best data from RapidAPI and Cardmarket';

    protected $totalUpdated = 0;

    public function handle(): int
    {
        $enrichImages = $this->option('images') || $this->option('all');
        $enrichPrices = $this->option('prices') || $this->option('all');
        $enrichLinks = $this->option('links') || $this->option('all');
        $enrichDetails = $this->option('details') || $this->option('all');

        if (!$enrichImages && !$enrichPrices && !$enrichLinks && !$enrichDetails) {
            $this->error('Please specify --images, --prices, --links, --details, or --all');
            return self::FAILURE;
        }

        $pipelineRun = PipelineRun::start('tcgcsv:enrich', [
            'images' => $enrichImages,
            'prices' => $enrichPrices,
            'links' => $enrichLinks,
            'details' => $enrichDetails,
        ]);

        $this->info('🚀 Enriching TCGCSV data...');
        $this->newLine();

        $this->totalUpdated = 0;

        if ($enrichImages) {
            $this->enrichImages();
        }

        if ($enrichPrices) {
            $this->enrichPrices();
        }

        if ($enrichLinks) {
            $this->enrichLinks();
        }

        if ($enrichDetails) {
            $this->enrichDetails();
        }

        // Mark pipeline run as success
        $pipelineRun->markSuccess([
            'rows_updated' => $this->totalUpdated,
        ]);

        return self::SUCCESS;
    }

    protected function enrichImages(): void
    {
        $this->info('🖼️  Enriching images with HD versions from RapidAPI...');

        // Get all mapped cards with RapidAPI images
        $mappings = DB::table('card_mappings as m')
            ->join('rapidapi_cards as r', 'm.rapidapi_card_id', '=', 'r.id')
            ->whereNotNull('r.image_url')
            ->whereNotNull('m.tcgcsv_product_id')
            ->select('m.tcgcsv_product_id', 'r.image_url')
            ->get();

        $this->line("Found {$mappings->count()} cards with HD images");

        $updated = 0;
        $progressBar = $this->output->createProgressBar($mappings->count());
        $progressBar->start();

        foreach ($mappings as $mapping) {
            DB::table('tcgcsv_products')
                ->where('product_id', $mapping->tcgcsv_product_id)
                ->update([
                    'hd_image_url' => $mapping->image_url,
                    'image_source' => 'rapidapi',
                    'updated_at' => now(),
                ]);
            
            $updated++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Updated {$updated} cards with HD images");
        $this->totalUpdated += $updated;
    }

    protected function enrichPrices(): void
    {
        $this->info('💰 Enriching with Cardmarket prices...');

        // Add price fields if they don't exist
        if (!DB::getSchemaBuilder()->hasColumn('tcgcsv_products', 'cardmarket_price_eur')) {
            $this->warn('Adding price columns to tcgcsv_products table...');
            
            DB::statement('ALTER TABLE tcgcsv_products 
                ADD COLUMN cardmarket_price_eur DECIMAL(10,2) NULL AFTER hd_image_url,
                ADD COLUMN cardmarket_price_updated_at TIMESTAMP NULL AFTER cardmarket_price_eur
            ');
        }

        // Get prices from RapidAPI
        $mappings = DB::table('card_mappings as m')
            ->join('rapidapi_cards as r', 'm.rapidapi_card_id', '=', 'r.id')
            ->whereNotNull('r.price_eur')
            ->whereNotNull('m.tcgcsv_product_id')
            ->select('m.tcgcsv_product_id', 'r.price_eur', 'r.last_synced_at')
            ->get();

        $this->line("Found {$mappings->count()} cards with prices");

        $updated = 0;
        $progressBar = $this->output->createProgressBar($mappings->count());
        $progressBar->start();

        foreach ($mappings as $mapping) {
            DB::table('tcgcsv_products')
                ->where('product_id', $mapping->tcgcsv_product_id)
                ->update([
                    'cardmarket_price_eur' => $mapping->price_eur,
                    'cardmarket_price_updated_at' => $mapping->last_synced_at,
                    'updated_at' => now(),
                ]);
            
            $updated++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Updated {$updated} cards with Cardmarket prices");
        $this->totalUpdated += $updated;
    }

    protected function enrichLinks(): void
    {
        $this->info('🔗 Enriching with external links (TCGO, Cardmarket)...');

        // Get mapped cards with URLs
        $mappings = DB::table('card_mappings as m')
            ->join('rapidapi_cards as r', 'm.rapidapi_card_id', '=', 'r.id')
            ->whereNotNull('m.tcgcsv_product_id')
            ->select(
                'm.tcgcsv_product_id',
                'r.image_url as tcgo_url',
                'r.cardmarket_url'
            )
            ->get();

        $this->line("Found {$mappings->count()} cards with external links");

        $updated = 0;
        $progressBar = $this->output->createProgressBar($mappings->count());
        $progressBar->start();

        foreach ($mappings as $mapping) {
            $updateData = ['updated_at' => now()];
            
            if ($mapping->tcgo_url) {
                $updateData['tcgo_url'] = $mapping->tcgo_url;
            }
            
            if ($mapping->cardmarket_url) {
                $updateData['cardmarket_url'] = $mapping->cardmarket_url;
            }

            DB::table('tcgcsv_products')
                ->where('product_id', $mapping->tcgcsv_product_id)
                ->update($updateData);
            
            $updated++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Updated {$updated} cards with external links");
        $this->totalUpdated += $updated;
    }

    protected function enrichDetails(): void
    {
        $this->info('📝 Enriching with card details (HP, artist, supertype, rarity)...');

        // Get mapped cards with details
        $mappings = DB::table('card_mappings as m')
            ->join('rapidapi_cards as r', 'm.rapidapi_card_id', '=', 'r.id')
            ->whereNotNull('m.tcgcsv_product_id')
            ->select(
                'm.tcgcsv_product_id',
                'r.hp',
                'r.artist',
                'r.supertype',
                'r.rarity'
            )
            ->get();

        $this->line("Found {$mappings->count()} cards with details");

        $updated = 0;
        $progressBar = $this->output->createProgressBar($mappings->count());
        $progressBar->start();

        foreach ($mappings as $mapping) {
            $updateData = ['updated_at' => now()];
            
            if ($mapping->hp) {
                $updateData['hp'] = $mapping->hp;
            }
            
            if ($mapping->artist) {
                $artistData = json_decode($mapping->artist, true);
                if ($artistData && isset($artistData['name'])) {
                    $updateData['artist_name'] = $artistData['name'];
                }
            }
            
            if ($mapping->supertype) {
                $updateData['supertype'] = $mapping->supertype;
            }
            
            if ($mapping->rarity) {
                $updateData['rapidapi_rarity'] = $mapping->rarity;
            }

            DB::table('tcgcsv_products')
                ->where('product_id', $mapping->tcgcsv_product_id)
                ->update($updateData);
            
            $updated++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Updated {$updated} cards with details");
        $this->totalUpdated += $updated;
    }
}
