<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CollectionInsightsService;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CollectionInsightsTest extends TestCase
{
    use RefreshDatabase;

    private CollectionInsightsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CollectionInsightsService();
    }

    /** @test */
    public function it_generates_dominant_rarity_insight()
    {
        $rarities = collect([
            (object)['rarity' => 'Common', 'total_quantity' => 60],
            (object)['rarity' => 'Rare', 'total_quantity' => 30],
            (object)['rarity' => 'Ultra Rare', 'total_quantity' => 10],
        ]);

        $insight = $this->service->generateRarityInsight($rarities);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('Common', $insight);
        $this->assertStringContainsString('mainly composed', $insight);
    }

    /** @test */
    public function it_generates_skewed_rarity_insight()
    {
        $rarities = collect([
            (object)['rarity' => 'Common', 'total_quantity' => 40],
            (object)['rarity' => 'Uncommon', 'total_quantity' => 30],
            (object)['rarity' => 'Rare', 'total_quantity' => 30],
        ]);

        $insight = $this->service->generateRarityInsight($rarities);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('Common', $insight);
        $this->assertStringContainsString('Uncommon', $insight);
    }

    /** @test */
    public function it_generates_balanced_rarity_insight()
    {
        $rarities = collect([
            (object)['rarity' => 'Common', 'total_quantity' => 25],
            (object)['rarity' => 'Uncommon', 'total_quantity' => 25],
            (object)['rarity' => 'Rare', 'total_quantity' => 25],
            (object)['rarity' => 'Ultra Rare', 'total_quantity' => 25],
        ]);

        $insight = $this->service->generateRarityInsight($rarities);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('balanced', $insight);
    }

    /** @test */
    public function it_returns_null_for_empty_rarity_distribution()
    {
        $rarities = collect([]);

        $insight = $this->service->generateRarityInsight($rarities);

        $this->assertNull($insight);
    }

    /** @test */
    public function it_generates_dominant_condition_insight()
    {
        $conditions = collect([
            (object)['condition' => 'near_mint', 'total_quantity' => 70],
            (object)['condition' => 'excellent', 'total_quantity' => 20],
            (object)['condition' => 'good', 'total_quantity' => 10],
        ]);

        $insight = $this->service->generateConditionInsight($conditions);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('Near Mint', $insight);
        $this->assertStringContainsString('Most cards', $insight);
    }

    /** @test */
    public function it_generates_balanced_condition_insight()
    {
        $conditions = collect([
            (object)['condition' => 'near_mint', 'total_quantity' => 30],
            (object)['condition' => 'excellent', 'total_quantity' => 35],
            (object)['condition' => 'good', 'total_quantity' => 35],
        ]);

        $insight = $this->service->generateConditionInsight($conditions);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('balanced', $insight);
    }

    /** @test */
    public function it_returns_null_for_empty_condition_distribution()
    {
        $conditions = collect([]);

        $insight = $this->service->generateConditionInsight($conditions);

        $this->assertNull($insight);
    }

    /** @test */
    public function it_generates_focus_candidate_sets_insight()
    {
        $sets = collect([
            (object)['group_id' => 1, 'name' => 'Base Set', 'owned_count' => 30, 'total_in_set' => 100, 'completion_percentage' => 30.0],
            (object)['group_id' => 2, 'name' => 'Jungle', 'owned_count' => 15, 'total_in_set' => 64, 'completion_percentage' => 23.4],
        ]);

        $focusSet = ['group_id' => 2, 'name' => 'Jungle', 'completion_percentage' => 23.4];

        $insight = $this->service->generateSetsInsight($sets, $focusSet);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('Jungle', $insight);
        $this->assertStringContainsString('closest', $insight);
    }

    /** @test */
    public function it_generates_progressing_sets_insight()
    {
        $sets = collect([
            (object)['group_id' => 1, 'name' => 'Base Set', 'owned_count' => 18, 'total_in_set' => 100, 'completion_percentage' => 18.0],
        ]);

        $focusSet = [];

        $insight = $this->service->generateSetsInsight($sets, $focusSet);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('Base Set', $insight);
        $this->assertStringContainsString('progress', $insight);
    }

    /** @test */
    public function it_generates_early_stage_sets_insight()
    {
        $sets = collect([
            (object)['group_id' => 1, 'name' => 'Base Set', 'owned_count' => 5, 'total_in_set' => 100, 'completion_percentage' => 5.0],
        ]);

        $focusSet = [];

        $insight = $this->service->generateSetsInsight($sets, $focusSet);

        $this->assertNotNull($insight);
        $this->assertStringContainsString('early', $insight);
    }

    /** @test */
    public function it_identifies_focus_set_with_highest_completion()
    {
        $sets = collect([
            (object)['group_id' => 1, 'name' => 'Base Set', 'owned_count' => 30, 'total_in_set' => 200, 'completion_percentage' => 15.0],
            (object)['group_id' => 2, 'name' => 'Jungle', 'owned_count' => 25, 'total_in_set' => 100, 'completion_percentage' => 25.0],
            (object)['group_id' => 3, 'name' => 'Fossil', 'owned_count' => 15, 'total_in_set' => 150, 'completion_percentage' => 10.0],
        ]);

        $focusSet = $this->service->identifyFocusSet($sets);

        $this->assertNotNull($focusSet);
        $this->assertEquals('Jungle', $focusSet['name']);
        $this->assertEquals(25.0, $focusSet['completion_percentage']);
    }

    /** @test */
    public function it_prefers_smaller_sets_when_completion_is_similar()
    {
        $sets = collect([
            (object)['group_id' => 1, 'name' => 'Large Set', 'owned_count' => 60, 'total_in_set' => 300, 'completion_percentage' => 20.0],
            (object)['group_id' => 2, 'name' => 'Small Set', 'owned_count' => 20, 'total_in_set' => 100, 'completion_percentage' => 20.0],
        ]);

        $focusSet = $this->service->identifyFocusSet($sets);

        $this->assertNotNull($focusSet);
        $this->assertEquals('Small Set', $focusSet['name']);
    }

    /** @test */
    public function it_returns_null_for_sets_below_minimum_completion()
    {
        $sets = collect([
            (object)['group_id' => 1, 'name' => 'Base Set', 'owned_count' => 5, 'total_in_set' => 100, 'completion_percentage' => 5.0],
        ]);

        $focusSet = $this->service->identifyFocusSet($sets);

        $this->assertNull($focusSet);
    }

    /** @test */
    public function it_relaxes_size_constraint_if_no_small_sets_qualify()
    {
        $sets = collect([
            (object)['group_id' => 1, 'name' => 'Huge Set', 'owned_count' => 50, 'total_in_set' => 500, 'completion_percentage' => 10.0],
        ]);

        $focusSet = $this->service->identifyFocusSet($sets);

        $this->assertNotNull($focusSet);
        $this->assertEquals('Huge Set', $focusSet['name']);
    }

    /** @test */
    public function it_formats_condition_names_correctly()
    {
        $this->assertEquals('Near Mint', $this->service->formatCondition('near_mint'));
        $this->assertEquals('Light Played', $this->service->formatCondition('light_played'));
        $this->assertEquals('Mint', $this->service->formatCondition('mint'));
    }
}
