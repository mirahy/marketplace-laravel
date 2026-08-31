<?php

namespace Tests\Feature;

use App\Filament\Resources\Listings\Pages\CreateListing;
use App\Filament\Resources\Listings\Pages\EditListing;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminListingSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_auto_generates_from_title_when_creating(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(CreateListing::class)
            ->set('data.title', 'Mesa de Escritório Bonita')
            ->assertSet('data.slug', 'mesa-de-escritorio-bonita');
    }

    public function test_slug_does_not_auto_update_when_editing_an_existing_listing(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $listing = Listing::factory()->create(['title' => 'Original', 'slug' => 'slug-original']);

        Livewire::actingAs($admin)
            ->test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->set('data.title', 'Novo Título')
            ->assertSet('data.slug', 'slug-original');
    }

    public function test_admin_can_create_a_listing_with_the_auto_generated_slug(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $seller = User::factory()->anunciante()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreateListing::class)
            ->fillForm([
                'title' => 'Item Teste',
                'slug' => 'item-teste',
                'user_id' => $seller->id,
                'category_id' => $category->id,
                'description' => 'Descrição.',
                'price' => 100,
                'condition' => 'novo',
                'status' => 'ativo',
                'city' => 'Navirai',
                'state' => 'MS',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('listings', ['title' => 'Item Teste', 'slug' => 'item-teste']);
    }

    public function test_admin_can_customize_and_persist_a_slug_when_creating(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $seller = User::factory()->anunciante()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreateListing::class)
            ->fillForm([
                'title' => 'Item Teste',
                'slug' => 'slug-personalizado',
                'user_id' => $seller->id,
                'category_id' => $category->id,
                'description' => 'Descrição.',
                'price' => 100,
                'condition' => 'novo',
                'status' => 'ativo',
                'city' => 'Navirai',
                'state' => 'MS',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('listings', ['title' => 'Item Teste', 'slug' => 'slug-personalizado']);
    }

    public function test_admin_can_edit_and_persist_a_listings_slug(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $listing = Listing::factory()->create(['slug' => 'slug-antigo']);

        Livewire::actingAs($admin)
            ->test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm(['slug' => 'slug-novo'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('slug-novo', $listing->fresh()->slug);
    }

    public function test_duplicate_slug_is_rejected_by_validation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $seller = User::factory()->anunciante()->create();
        $category = Category::factory()->create();
        Listing::factory()->create(['slug' => 'slug-existente']);

        Livewire::actingAs($admin)
            ->test(CreateListing::class)
            ->fillForm([
                'title' => 'Outro Item',
                'slug' => 'slug-existente',
                'user_id' => $seller->id,
                'category_id' => $category->id,
                'description' => 'Descrição.',
                'price' => 50,
                'condition' => 'novo',
                'status' => 'ativo',
                'city' => 'Navirai',
                'state' => 'MS',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }
}
