<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\RelationManagers\ListingsRelationManager;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserListingsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_user_page_shows_only_that_users_listings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownListing = Listing::factory()->create(['user_id' => $user->id, 'title' => 'Bicicleta do usuário']);
        $otherListing = Listing::factory()->create(['user_id' => $otherUser->id, 'title' => 'Sofá de outro usuário']);

        $this->actingAs($admin)->get("/admin/users/{$user->id}/edit")->assertOk();

        Livewire::actingAs($admin)
            ->test(ListingsRelationManager::class, [
                'ownerRecord' => $user,
                'pageClass' => EditUser::class,
            ])
            ->assertCanSeeTableRecords([$ownListing])
            ->assertCanNotSeeTableRecords([$otherListing]);
    }
}
