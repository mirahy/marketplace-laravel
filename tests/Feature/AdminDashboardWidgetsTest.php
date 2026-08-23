<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\UserStatus;
use App\Filament\Widgets\LatestListingsWidget;
use App\Filament\Widgets\PendingListingsWidget;
use App\Filament\Widgets\PendingUsersWidget;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\ListingStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_latest_listings_widget_shows_the_5_most_recent_listings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $older = Listing::factory()->create(['title' => 'Anúncio antigo', 'created_at' => now()->subDays(10)]);
        $recent = Listing::factory()->count(5)->create(['created_at' => now()]);

        Livewire::actingAs($admin)
            ->test(LatestListingsWidget::class)
            ->assertCanSeeTableRecords($recent)
            ->assertCanNotSeeTableRecords([$older]);
    }

    public function test_pending_listings_widget_can_approve_and_reject(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $pending = Listing::factory()->create(['status' => ListingStatus::EmAnalise]);
        $active = Listing::factory()->create(['status' => ListingStatus::Ativo]);

        $component = Livewire::actingAs($admin)->test(PendingListingsWidget::class);

        $component
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$active])
            ->callTableAction('approve', $pending);

        $this->assertSame(ListingStatus::Ativo, $pending->refresh()->status);
        Notification::assertSentTo($pending->user, ListingStatusUpdated::class);
    }

    public function test_pending_users_widget_can_approve_and_reject(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $pending = User::factory()->pending()->create();
        $approved = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(PendingUsersWidget::class)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$approved])
            ->callTableAction('reject', $pending);

        $this->assertSame(UserStatus::Rejeitado, $pending->refresh()->status);
    }
}
