<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Conversations\ConversationResource;
use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\LatestListingsWidget;
use App\Filament\Widgets\PendingCategoriesWidget;
use App\Filament\Widgets\PendingListingsWidget;
use App\Filament\Widgets\PendingUsersWidget;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTableRecordUrlTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_users_table_rows_link_to_the_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertOk()->assertSee('href="'.UserResource::getUrl('edit', ['record' => $user]).'"', false);
    }

    public function test_listings_table_rows_link_to_the_edit_page(): void
    {
        $listing = Listing::factory()->create(['status' => ListingStatus::Ativo]);

        $response = $this->actingAs($this->admin)->get('/admin/listings');

        $response->assertOk()->assertSee('href="'.ListingResource::getUrl('edit', ['record' => $listing]).'"', false);
    }

    public function test_categories_table_rows_link_to_the_edit_page(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertOk()->assertSee('href="'.CategoryResource::getUrl('edit', ['record' => $category]).'"', false);
    }

    public function test_conversations_table_rows_link_to_the_view_page(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id]);
        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/conversations');

        $response->assertOk()->assertSee('href="'.ConversationResource::getUrl('view', ['record' => $conversation]).'"', false);
    }

    public function test_latest_listings_widget_rows_link_to_the_edit_page(): void
    {
        $listing = Listing::factory()->create(['status' => ListingStatus::Ativo]);

        $this->actingAs($this->admin);

        Livewire::test(LatestListingsWidget::class)
            ->assertSee('href="'.ListingResource::getUrl('edit', ['record' => $listing]).'"', false);
    }

    public function test_pending_listings_widget_rows_link_to_the_edit_page(): void
    {
        $listing = Listing::factory()->create(['status' => ListingStatus::EmAnalise]);

        $this->actingAs($this->admin);

        Livewire::test(PendingListingsWidget::class)
            ->assertSee('href="'.ListingResource::getUrl('edit', ['record' => $listing]).'"', false);
    }

    public function test_pending_users_widget_rows_link_to_the_edit_page(): void
    {
        $pendingUser = User::factory()->create(['status' => \App\Enums\UserStatus::Pendente]);

        $this->actingAs($this->admin);

        Livewire::test(PendingUsersWidget::class)
            ->assertSee('href="'.UserResource::getUrl('edit', ['record' => $pendingUser]).'"', false);
    }

    public function test_pending_categories_widget_rows_link_to_the_edit_page(): void
    {
        $pendingCategory = Category::factory()->create(['status' => \App\Enums\CategoryStatus::Pendente]);

        $this->actingAs($this->admin);

        Livewire::test(PendingCategoriesWidget::class)
            ->assertSee('href="'.CategoryResource::getUrl('edit', ['record' => $pendingCategory]).'"', false);
    }
}
