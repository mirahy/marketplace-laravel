<?php

namespace Tests\Feature;

use App\Filament\Resources\Listings\Pages\EditListing;
use App\Filament\Resources\Listings\RelationManagers\ImagesRelationManager;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListingImageLightboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_clicking_an_image_opens_a_modal_with_all_the_listings_photos_starting_at_the_clicked_one(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $listing = Listing::factory()->create();

        $first = ListingImage::factory()->create(['listing_id' => $listing->id, 'order' => 0]);
        $second = ListingImage::factory()->create(['listing_id' => $listing->id, 'order' => 1]);
        $third = ListingImage::factory()->create(['listing_id' => $listing->id, 'order' => 2]);

        $this->actingAs($admin)->get("/admin/listings/{$listing->id}/edit")->assertOk();

        $relationManager = Livewire::actingAs($admin)
            ->test(ImagesRelationManager::class, [
                'ownerRecord' => $listing,
                'pageClass' => EditListing::class,
            ])
            ->instance();

        $action = $relationManager->getTable()->getColumn('path')->getAction();
        $action->record($second);

        $html = (string) $action->getModalContent()->render();

        $this->assertStringContainsString($first->path, $html);
        $this->assertStringContainsString($second->path, $html);
        $this->assertStringContainsString($third->path, $html);

        // The modal must open already positioned on the image that was
        // clicked (the second one, index 1), not always on the first image.
        $this->assertStringContainsString('active: 1', $html);
    }

    public function test_clicking_the_first_image_opens_the_modal_at_index_zero(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $listing = Listing::factory()->create();

        $first = ListingImage::factory()->create(['listing_id' => $listing->id, 'order' => 0]);
        ListingImage::factory()->create(['listing_id' => $listing->id, 'order' => 1]);

        $relationManager = Livewire::actingAs($admin)
            ->test(ImagesRelationManager::class, [
                'ownerRecord' => $listing,
                'pageClass' => EditListing::class,
            ])
            ->instance();

        $action = $relationManager->getTable()->getColumn('path')->getAction();
        $action->record($first);

        $html = (string) $action->getModalContent()->render();

        $this->assertStringContainsString('active: 0', $html);
    }
}
