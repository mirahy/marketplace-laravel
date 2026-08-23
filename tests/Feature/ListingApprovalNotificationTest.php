<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Filament\Resources\Listings\Pages\ListListings;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\ListingStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ListingApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_is_notified_when_admin_approves_listing(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $seller = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $seller->id,
            'status' => ListingStatus::EmAnalise,
        ]);

        Livewire::actingAs($admin)
            ->test(ListListings::class)
            ->callTableAction('approve', $listing);

        $this->assertSame(ListingStatus::Ativo, $listing->fresh()->status);

        Notification::assertSentTo($seller, ListingStatusUpdated::class);
    }

    public function test_seller_is_notified_when_admin_rejects_listing(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $seller = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $seller->id,
            'status' => ListingStatus::EmAnalise,
        ]);

        Livewire::actingAs($admin)
            ->test(ListListings::class)
            ->callTableAction('reject', $listing);

        $this->assertSame(ListingStatus::Rejeitado, $listing->fresh()->status);

        Notification::assertSentTo($seller, ListingStatusUpdated::class);
    }
}
