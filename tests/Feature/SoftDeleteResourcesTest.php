<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Conversations\Pages\ListConversations;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SoftDeleteResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_soft_deleted_restored_and_force_deleted(): void
    {
        $user = User::factory()->create();

        $user->delete();
        $this->assertSoftDeleted($user);

        $user->restore();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);

        $user->forceDelete();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_category_can_be_soft_deleted_and_restored(): void
    {
        $category = Category::factory()->create();

        $category->delete();
        $this->assertSoftDeleted($category);

        $category->restore();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_conversation_can_be_soft_deleted_and_restored(): void
    {
        $listing = Listing::factory()->create();
        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => User::factory()->create()->id,
            'seller_id' => $listing->user_id,
        ]);

        $conversation->delete();
        $this->assertSoftDeleted($conversation);

        $conversation->restore();
        $this->assertDatabaseHas('conversations', ['id' => $conversation->id, 'deleted_at' => null]);
    }

    public function test_admin_can_soft_delete_and_restore_a_user_from_the_table_bulk_action(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableBulkAction('delete', [$user]);

        $this->assertSoftDeleted($user);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->filterTable('trashed', false)
            ->callTableBulkAction('restore', [$user]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_admin_can_soft_delete_a_category_from_the_table_bulk_action(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test(ListCategories::class)
            ->callTableBulkAction('delete', [$category]);

        $this->assertSoftDeleted($category);
    }

    public function test_admin_can_soft_delete_a_conversation_from_the_table_bulk_action(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $listing = Listing::factory()->create();
        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => User::factory()->create()->id,
            'seller_id' => $listing->user_id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListConversations::class)
            ->callTableBulkAction('delete', [$conversation]);

        $this->assertSoftDeleted($conversation);
    }
}
