<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Livewire\NotificationBell;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\ListingStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_and_read_a_database_notification(): void
    {
        $seller = User::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $seller->id,
            'status' => ListingStatus::Ativo,
        ]);

        $seller->notify(new ListingStatusUpdated($listing));

        $this->assertSame(1, $seller->fresh()->unreadNotifications()->count());

        $notification = $seller->notifications()->first();

        Livewire::actingAs($seller)
            ->test(NotificationBell::class)
            ->assertSee('Anúncio aprovado')
            ->call('markAsRead', $notification->id)
            ->assertRedirect('/anuncios/'.$listing->slug);

        $this->assertSame(0, $seller->fresh()->unreadNotifications()->count());
    }
}
