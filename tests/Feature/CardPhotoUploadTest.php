<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\PricingPlan;
use App\Models\TcgcsvProduct;
use App\Models\User;
use App\Models\UserCardPhoto;
use App\Models\UserCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CardPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create pricing plans
        PricingPlan::factory()->free()->create(['name' => 'Free Plan']);
        PricingPlan::factory()->advanced()->create(['name' => 'Advanced Plan']);
        PricingPlan::factory()->premium()->create(['name' => 'Premium Plan']);
        
        // Set up test games
        \DB::table('games')->insert([
            ['id' => 1, 'name' => 'Pokemon', 'code' => 'pokemon', 'slug' => 'pokemon', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Create test product
        TcgcsvProduct::create([
            'product_id' => 1,
            'group_id' => 1,
            'game_id' => 1,
            'name' => 'Test Card',
            'category_id' => 3,
        ]);
        
        // Fake storage
        Storage::fake('private');
    }

    protected function createUserWithTier(string $tier): User
    {
        $user = User::factory()->create();
        
        if ($tier !== 'free') {
            $plan = PricingPlan::where('name', ucfirst($tier) . ' Plan')->first();
            $organization = Organization::factory()->create([
                'name' => $user->name . "'s Organization",
                'pricing_plan_id' => $plan->id,
            ]);
            $user->update(['organization_id' => $organization->id]);
        }
        
        return $user;
    }

    /** @test */
    public function free_user_cannot_upload_card_photo()
    {
        $user = $this->createUserWithTier('free');
        
        $collection = UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file = UploadedFile::fake()->image('card.jpg');

        $response = $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), [
                'photo' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Verify no photo was created
        $this->assertEquals(0, UserCardPhoto::count());
        Storage::disk('private')->assertMissing('user-card-photos/' . $user->id . '/' . $file->hashName());
    }

    /** @test */
    public function advanced_user_cannot_upload_card_photo()
    {
        $user = $this->createUserWithTier('advanced');
        
        $collection = UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file = UploadedFile::fake()->image('card.jpg');

        $response = $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), [
                'photo' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Verify no photo was created
        $this->assertEquals(0, UserCardPhoto::count());
        Storage::disk('private')->assertMissing('user-card-photos/' . $user->id . '/' . $file->hashName());
    }

    /** @test */
    public function premium_user_can_upload_card_photo()
    {
        $user = $this->createUserWithTier('premium');
        
        $collection = UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file = UploadedFile::fake()->image('card.jpg', 800, 600);

        $response = $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), [
                'photo' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Verify photo was created
        $this->assertEquals(1, UserCardPhoto::count());
        
        $photo = UserCardPhoto::first();
        $this->assertEquals($user->id, $photo->user_id);
        $this->assertEquals($collection->id, $photo->user_collection_id);
        $this->assertEquals('card.jpg', $photo->original_filename);
        $this->assertEquals('image/jpeg', $photo->mime_type);
        $this->assertGreaterThan(0, $photo->size_bytes);
        
        // Verify file was stored
        Storage::disk('private')->assertExists($photo->path);
    }

    /** @test */
    public function non_owner_cannot_upload_photo_to_someone_elses_collection()
    {
        $owner = $this->createUserWithTier('premium');
        $otherUser = $this->createUserWithTier('premium');
        
        $collection = UserCollection::create([
            'user_id' => $owner->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file = UploadedFile::fake()->image('card.jpg');

        $response = $this->actingAs($otherUser)
            ->post(route('collection.photos.upload', $collection), [
                'photo' => $file,
            ]);

        $response->assertForbidden();
        
        // Verify no photo was created
        $this->assertEquals(0, UserCardPhoto::count());
    }

    /** @test */
    public function premium_user_can_upload_multiple_photos_for_same_card()
    {
        $user = $this->createUserWithTier('premium');
        
        $collection = UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file1 = UploadedFile::fake()->image('card-front.jpg');
        $file2 = UploadedFile::fake()->image('card-back.jpg');

        // Upload first photo
        $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), ['photo' => $file1])
            ->assertSessionHas('success');
        
        // Upload second photo
        $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), ['photo' => $file2])
            ->assertSessionHas('success');
        
        // Verify both photos were created
        $this->assertEquals(2, UserCardPhoto::count());
        $this->assertEquals(2, $collection->photos()->count());
    }

    /** @test */
    public function upload_validates_file_type()
    {
        $user = $this->createUserWithTier('premium');
        
        $collection = UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), [
                'photo' => $file,
            ]);

        $response->assertSessionHasErrors('photo');
        $this->assertEquals(0, UserCardPhoto::count());
    }

    /** @test */
    public function upload_validates_file_size()
    {
        $user = $this->createUserWithTier('premium');
        
        $collection = UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        // Create a file larger than 5MB
        $file = UploadedFile::fake()->image('huge-card.jpg')->size(6000);

        $response = $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), [
                'photo' => $file,
            ]);

        $response->assertSessionHasErrors('photo');
        $this->assertEquals(0, UserCardPhoto::count());
    }

    /** @test */
    public function owner_can_delete_photo()
    {
        $user = $this->createUserWithTier('premium');
        
        $collection = UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file = UploadedFile::fake()->image('card.jpg');
        
        // Upload photo
        $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), ['photo' => $file]);
        
        $photo = UserCardPhoto::first();
        $photoPath = $photo->path;
        
        // Verify file exists
        Storage::disk('private')->assertExists($photoPath);
        
        // Delete photo
        $response = $this->actingAs($user)
            ->delete(route('collection.photos.delete', $photo));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Verify photo was deleted
        $this->assertEquals(0, UserCardPhoto::count());
        
        // Verify file was deleted
        Storage::disk('private')->assertMissing($photoPath);
    }

    /** @test */
    public function non_owner_cannot_delete_someone_elses_photo()
    {
        $owner = $this->createUserWithTier('premium');
        $otherUser = $this->createUserWithTier('premium');
        
        $collection = UserCollection::create([
            'user_id' => $owner->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file = UploadedFile::fake()->image('card.jpg');
        
        // Upload photo as owner
        $this->actingAs($owner)
            ->post(route('collection.photos.upload', $collection), ['photo' => $file]);
        
        $photo = UserCardPhoto::first();
        
        // Try to delete as other user
        $response = $this->actingAs($otherUser)
            ->delete(route('collection.photos.delete', $photo));

        $response->assertForbidden();
        
        // Verify photo still exists
        $this->assertEquals(1, UserCardPhoto::count());
    }

    /** @test */
    public function photo_deletion_cascades_when_collection_item_is_deleted()
    {
        $user = $this->createUserWithTier('premium');
        
        $collection = UserCollection::create([
            'user_id' => $user->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $file = UploadedFile::fake()->image('card.jpg');
        
        // Upload photo
        $this->actingAs($user)
            ->post(route('collection.photos.upload', $collection), ['photo' => $file]);
        
        $photo = UserCardPhoto::first();
        $photoPath = $photo->path;
        
        // Delete collection item
        $collection->delete();
        
        // Verify photo was deleted
        $this->assertEquals(0, UserCardPhoto::count());
        
        // Verify file was deleted
        Storage::disk('private')->assertMissing($photoPath);
    }
}
