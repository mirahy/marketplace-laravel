<?php

namespace Tests\Feature;

use App\Filament\Resources\Listings\Pages\CreateListing;
use App\Filament\Resources\Listings\Pages\EditListing;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminFormFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_show_phone_to_guests_on_the_user_edit_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['show_phone_to_guests' => false]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['show_phone_to_guests' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($user->fresh()->show_phone_to_guests);
    }

    public function test_admin_can_fill_all_address_fields_when_creating_a_listing(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $seller = User::factory()->anunciante()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreateListing::class)
            ->fillForm([
                'title' => 'Sofá de três lugares',
                'slug' => 'sofa-de-tres-lugares',
                'user_id' => $seller->id,
                'category_id' => $category->id,
                'description' => 'Em ótimo estado.',
                'price' => 850,
                'condition' => 'usado',
                'status' => 'ativo',
                'city' => 'Navirai',
                'state' => 'MS',
                'address_type' => 'rua',
                'address_street' => 'das Flores',
                'address_number' => '123',
                'address_neighborhood' => 'Centro',
                'address_complement' => 'Apto 12',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $listing = Listing::query()->where('title', 'Sofá de três lugares')->firstOrFail();

        $this->assertSame('rua', $listing->address_type->value);
        $this->assertSame('das Flores', $listing->address_street);
        $this->assertSame('123', $listing->address_number);
        $this->assertSame('Centro', $listing->address_neighborhood);
        $this->assertSame('Apto 12', $listing->address_complement);
    }

    public function test_admin_can_edit_the_address_fields_of_an_existing_listing(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $listing = Listing::factory()->create();

        Livewire::actingAs($admin)
            ->test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm([
                'address_type' => 'avenida',
                'address_street' => 'Brasil',
                'address_number' => '456',
                'address_neighborhood' => 'Jardim',
                'address_complement' => 'Fundos',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $listing->refresh();

        $this->assertSame('avenida', $listing->address_type->value);
        $this->assertSame('Brasil', $listing->address_street);
        $this->assertSame('456', $listing->address_number);
        $this->assertSame('Jardim', $listing->address_neighborhood);
        $this->assertSame('Fundos', $listing->address_complement);
    }
}
