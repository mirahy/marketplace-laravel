<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Livewire\ListingForm;
use App\Livewire\ListingShow;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_cannot_create_a_listing_and_gets_403(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->hasRole('usuario'));

        $this->actingAs($user)->get('/anuncios/novo')->assertForbidden();
    }

    public function test_anunciante_can_create_a_listing(): void
    {
        $user = User::factory()->anunciante()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($user)
            ->test(ListingForm::class)
            ->set('title', 'Cadeira de escritório')
            ->set('description', 'Em bom estado.')
            ->set('price', 250)
            ->set('condition', 'usado')
            ->set('categoryId', $category->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('listings.mine'));

        $this->assertDatabaseHas('listings', ['title' => 'Cadeira de escritório', 'user_id' => $user->id]);
    }

    public function test_admin_can_create_a_listing_regardless_of_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();

        $this->assertTrue($admin->hasRole('usuario'));

        Livewire::actingAs($admin)
            ->test(ListingForm::class)
            ->set('title', 'Ferramenta doada')
            ->set('description', 'Em bom estado.')
            ->set('price', 50)
            ->set('condition', 'usado')
            ->set('categoryId', $category->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('listings', ['title' => 'Ferramenta doada', 'user_id' => $admin->id]);
    }

    public function test_all_roles_can_send_a_message_as_buyer(): void
    {
        $seller = User::factory()->anunciante()->create();

        foreach (['usuario', 'anunciante', 'admin'] as $case) {
            $buyer = $case === 'admin'
                ? User::factory()->create(['is_admin' => true])
                : ($case === 'anunciante' ? User::factory()->anunciante()->create() : User::factory()->create());

            $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

            Livewire::actingAs($buyer)
                ->test(ListingShow::class, ['listing' => $listing])
                ->call('sendMessage');

            $this->assertDatabaseHas('conversations', [
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
            ]);
        }
    }

    public function test_filament_edit_user_assigns_the_selected_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->assertTrue($user->hasRole('usuario'));

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['role' => 'anunciante'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($user->fresh()->hasRole('anunciante'));
        $this->assertFalse($user->fresh()->hasRole('usuario'));
    }
}
