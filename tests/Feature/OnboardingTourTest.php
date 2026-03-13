<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_onboarding_status(): void
    {
        $this->postJson('/onboarding-tour/completed')->assertStatus(401);
        $this->postJson('/onboarding-tour/skipped')->assertStatus(401);
        $this->postJson('/onboarding-tour/reset')->assertStatus(401);
    }

    public function test_authenticated_user_can_mark_tour_completed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/onboarding-tour/completed')
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'completed']);

        $user->refresh();

        $this->assertNotNull($user->onboarding_tour_completed_at);
    }

    public function test_authenticated_user_can_mark_tour_skipped(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/onboarding-tour/skipped')
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'skipped']);

        $user->refresh();

        $this->assertNotNull($user->onboarding_tour_skipped_at);
    }

    public function test_authenticated_user_can_reset_onboarding_state(): void
    {
        $user = User::factory()->create([
            'onboarding_tour_completed_at' => now(),
            'onboarding_tour_skipped_at' => now(),
        ]);

        $this->actingAs($user)
            ->postJson('/onboarding-tour/reset')
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'reset']);

        $user->refresh();

        $this->assertNull($user->onboarding_tour_completed_at);
        $this->assertNull($user->onboarding_tour_skipped_at);
    }
}

