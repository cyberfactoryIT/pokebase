<?php

namespace Tests\Feature\Cardmarket;

use App\Models\CardmarketImportRun;
use App\Models\CardmarketProduct;
use App\Models\CardmarketPriceQuote;
use App\Services\Cardmarket\CardmarketImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardmarketImportTest extends TestCase
{
    use RefreshDatabase;

    protected string $cataloguePath;
    protected string $priceGuidePath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cataloguePath = base_path('tests/Fixtures/cardmarket/catalogue_test.csv');
        $this->priceGuidePath = base_path('tests/Fixtures/cardmarket/priceguide_test.csv');
    }

    public function test_can_import_catalogue_from_fixture(): void
    {
        $importer = app(CardmarketImporter::class);
        
        $run = CardmarketImportRun::create([
            'type' => 'catalogue',
            'status' => 'running',
        ]);

        $result = $importer->importCatalogue($this->cataloguePath, $run);

        $this->assertTrue($result['success']);
        $this->assertEquals(8, $result['rows_read']);
        $this->assertEquals(8, $result['rows_upserted']);

        // Verify products were imported
        $this->assertDatabaseCount('cardmarket_products', 8);
        
        // Verify specific product
        $charizard = CardmarketProduct::where('cardmarket_product_id', 1)->first();
        $this->assertNotNull($charizard);
        $this->assertEquals('Pokemon', $charizard->game);
        $this->assertEquals('Charizard', $charizard->name);
        $this->assertEquals('Base Set', $charizard->expansion);
        $this->assertFalse($charizard->is_foil);

        // Verify foil variant
        $charizardFoil = CardmarketProduct::where('cardmarket_product_id', 7)->first();
        $this->assertTrue($charizardFoil->is_foil);

        // Verify run was updated
        $run->refresh();
        $this->assertEquals(8, $run->rows_read);
        $this->assertEquals(8, $run->rows_upserted);
    }

    public function test_can_import_price_guide_from_fixture(): void
    {
        // First import catalogue to have products
        $this->test_can_import_catalogue_from_fixture();

        $importer = app(CardmarketImporter::class);
        
        $run = CardmarketImportRun::create([
            'type' => 'priceguide',
            'status' => 'running',
        ]);

        $asOfDate = '2025-12-27';
        $result = $importer->importPriceGuide($this->priceGuidePath, $run, $asOfDate);

        $this->assertTrue($result['success']);
        $this->assertEquals(8, $result['rows_read']);
        $this->assertEquals(8, $result['rows_upserted']);
        $this->assertEquals($asOfDate, $result['as_of_date']);

        // Verify price quotes were imported
        $this->assertDatabaseCount('cardmarket_price_quotes', 8);
        
        // Verify specific price quote
        $charizardPrice = CardmarketPriceQuote::where('cardmarket_product_id', 1)
            ->where('as_of_date', $asOfDate)
            ->first();
        
        $this->assertNotNull($charizardPrice);
        $this->assertEquals(150.50, $charizardPrice->low_price);
        $this->assertEquals(200.00, $charizardPrice->avg_price);
        $this->assertEquals(195.00, $charizardPrice->trend_price);
        $this->assertEquals('EUR', $charizardPrice->currency);
    }

    public function test_import_is_idempotent(): void
    {
        $importer = app(CardmarketImporter::class);
        
        $run = CardmarketImportRun::create([
            'type' => 'catalogue',
            'status' => 'running',
        ]);

        // Import twice
        $result1 = $importer->importCatalogue($this->cataloguePath, $run);
        $result2 = $importer->importCatalogue($this->cataloguePath, $run);

        $this->assertTrue($result1['success']);
        $this->assertTrue($result2['success']);

        // Should still only have 8 products (no duplicates)
        $this->assertDatabaseCount('cardmarket_products', 8);
    }

    public function test_price_quotes_maintain_historical_snapshots(): void
    {
        // Import catalogue
        $this->test_can_import_catalogue_from_fixture();

        $importer = app(CardmarketImporter::class);
        
        $run1 = CardmarketImportRun::create(['type' => 'priceguide', 'status' => 'running']);
        $run2 = CardmarketImportRun::create(['type' => 'priceguide', 'status' => 'running']);

        // Import prices for two different dates
        $importer->importPriceGuide($this->priceGuidePath, $run1, '2025-12-27');
        $importer->importPriceGuide($this->priceGuidePath, $run2, '2025-12-28');

        // Should have 16 price quotes (8 products × 2 dates)
        $this->assertDatabaseCount('cardmarket_price_quotes', 16);

        // Verify both dates exist for same product
        $charizardQuotes = CardmarketPriceQuote::where('cardmarket_product_id', 1)->get();
        $this->assertCount(2, $charizardQuotes);
    }

    public function test_dry_run_does_not_persist_data(): void
    {
        $importer = app(CardmarketImporter::class);
        
        $run = CardmarketImportRun::create([
            'type' => 'catalogue',
            'status' => 'running',
        ]);

        $result = $importer->importCatalogue($this->cataloguePath, $run, true);

        $this->assertTrue($result['success']);
        $this->assertEquals(8, $result['rows_read']);

        // Should not have persisted any products
        $this->assertDatabaseCount('cardmarket_products', 0);
    }
}
