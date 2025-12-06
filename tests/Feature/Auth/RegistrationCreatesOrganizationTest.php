<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationCreatesOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_registration_creates_organization_and_admin_user()
    {
        $response = $this->post('/register', [
            'organization_name' => 'Test Org',
            'organization_code' => 'ORG123',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $org = Organization::where('name', 'Test Org')->where('code', 'ORG123')->first();
        $this->assertNotNull($org);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($org->id, $user->organization_id);

        if (class_exists(Role::class)) {
            $this->assertTrue($user->hasRole('admin'));
        } elseif (Schema::hasColumn('users', 'role')) {
            $this->assertEquals('admin', $user->role);
        }
    }

    public function test_duplicate_organization_registration_fails_and_no_extra_records()
    {
        // First registration
        $this->post('/register', [
            'organization_name' => 'Test Org',
            'organization_code' => 'ORG123',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Attempt duplicate registration
        $response = $this->post('/register', [
            'organization_name' => 'Test Org',
            'organization_code' => 'ORG123',
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['organization_name']);
        $this->assertEquals(1, Organization::where('name', 'Test Org')->where('code', 'ORG123')->count());
        $this->assertEquals(1, User::where('organization_id', Organization::where('name', 'Test Org')->where('code', 'ORG123')->first()->id)->count());
    }
}
