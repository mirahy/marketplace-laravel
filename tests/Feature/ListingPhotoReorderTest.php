<?php

namespace Tests\Feature;

use App\Livewire\ListingForm;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ListingPhotoReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_move_an_existing_photo_backward_and_it_persists_immediately(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id]);

        $first = $listing->images()->create(['path' => 'listings/first.jpg', 'order' => 0]);
        $second = $listing->images()->create(['path' => 'listings/second.jpg', 'order' => 1]);
        $third = $listing->images()->create(['path' => 'listings/third.jpg', 'order' => 2]);

        Livewire::actingAs($owner)
            ->test(ListingForm::class, ['listing' => $listing])
            ->call('moveExistingImage', $second->id, -1);

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
        $this->assertSame(2, $third->fresh()->order);
    }

    public function test_owner_can_move_an_existing_photo_forward_and_it_persists_immediately(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id]);

        $first = $listing->images()->create(['path' => 'listings/first.jpg', 'order' => 0]);
        $second = $listing->images()->create(['path' => 'listings/second.jpg', 'order' => 1]);

        Livewire::actingAs($owner)
            ->test(ListingForm::class, ['listing' => $listing])
            ->call('moveExistingImage', $first->id, 1);

        $this->assertSame(1, $first->fresh()->order);
        $this->assertSame(0, $second->fresh()->order);
    }

    public function test_moving_the_first_existing_photo_backward_does_nothing(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id]);

        $first = $listing->images()->create(['path' => 'listings/first.jpg', 'order' => 0]);
        $second = $listing->images()->create(['path' => 'listings/second.jpg', 'order' => 1]);

        Livewire::actingAs($owner)
            ->test(ListingForm::class, ['listing' => $listing])
            ->call('moveExistingImage', $first->id, -1);

        $this->assertSame(0, $first->fresh()->order);
        $this->assertSame(1, $second->fresh()->order);
    }

    public function test_moving_the_last_existing_photo_forward_does_nothing(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id]);

        $first = $listing->images()->create(['path' => 'listings/first.jpg', 'order' => 0]);
        $second = $listing->images()->create(['path' => 'listings/second.jpg', 'order' => 1]);

        Livewire::actingAs($owner)
            ->test(ListingForm::class, ['listing' => $listing])
            ->call('moveExistingImage', $second->id, 1);

        $this->assertSame(0, $first->fresh()->order);
        $this->assertSame(1, $second->fresh()->order);
    }

    public function test_reordering_updates_the_components_existing_images_list(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id]);

        $first = $listing->images()->create(['path' => 'listings/first.jpg', 'order' => 0]);
        $second = $listing->images()->create(['path' => 'listings/second.jpg', 'order' => 1]);

        $component = Livewire::actingAs($owner)
            ->test(ListingForm::class, ['listing' => $listing])
            ->call('moveExistingImage', $first->id, 1);

        $orderedIds = collect($component->get('existingImages'))->pluck('id')->all();

        $this->assertSame([$second->id, $first->id], $orderedIds);
    }

    public function test_pending_new_photos_can_be_reordered_before_saving(): void
    {
        Storage::fake('public');
        $user = User::factory()->anunciante()->create();

        $first = UploadedFile::fake()->image('first.jpg');
        $second = UploadedFile::fake()->image('second.jpg');

        $component = Livewire::actingAs($user)
            ->test(ListingForm::class)
            ->set('newPhotos', [$first, $second])
            ->call('moveNewPhoto', 0, 1);

        $photos = $component->get('newPhotos');

        $this->assertSame('second.jpg', $photos[0]->getClientOriginalName());
        $this->assertSame('first.jpg', $photos[1]->getClientOriginalName());
    }

    public function test_moving_the_first_pending_photo_backward_does_nothing(): void
    {
        Storage::fake('public');
        $user = User::factory()->anunciante()->create();

        $first = UploadedFile::fake()->image('first.jpg');
        $second = UploadedFile::fake()->image('second.jpg');

        $component = Livewire::actingAs($user)
            ->test(ListingForm::class)
            ->set('newPhotos', [$first, $second])
            ->call('moveNewPhoto', 0, -1);

        $photos = $component->get('newPhotos');

        $this->assertSame('first.jpg', $photos[0]->getClientOriginalName());
        $this->assertSame('second.jpg', $photos[1]->getClientOriginalName());
    }

    public function test_moving_the_last_pending_photo_forward_does_nothing(): void
    {
        Storage::fake('public');
        $user = User::factory()->anunciante()->create();

        $first = UploadedFile::fake()->image('first.jpg');
        $second = UploadedFile::fake()->image('second.jpg');

        $component = Livewire::actingAs($user)
            ->test(ListingForm::class)
            ->set('newPhotos', [$first, $second])
            ->call('moveNewPhoto', 1, 1);

        $photos = $component->get('newPhotos');

        $this->assertSame('first.jpg', $photos[0]->getClientOriginalName());
        $this->assertSame('second.jpg', $photos[1]->getClientOriginalName());
    }
}
