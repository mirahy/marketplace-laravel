<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Livewire\Home;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_with_an_active_listing_appears_on_the_home_page(): void
    {
        $category = Category::factory()->create(['name' => 'Eletrodomésticos']);
        Listing::factory()->create(['category_id' => $category->id, 'status' => ListingStatus::Ativo]);

        $this->get('/')->assertOk()->assertSee('Eletrodomésticos');
    }

    public function test_category_without_any_listing_does_not_appear_on_the_home_page(): void
    {
        Category::factory()->create(['name' => 'Categoria Vazia']);

        $this->get('/')->assertOk()->assertDontSee('Categoria Vazia');
    }

    public function test_category_without_an_active_listing_does_not_appear_on_the_home_page(): void
    {
        $category = Category::factory()->create(['name' => 'Só Pendentes']);
        Listing::factory()->create(['category_id' => $category->id, 'status' => ListingStatus::EmAnalise]);
        Listing::factory()->create(['category_id' => $category->id, 'status' => ListingStatus::Rejeitado]);

        $this->get('/')->assertOk()->assertDontSee('Só Pendentes');
    }

    public function test_category_whose_only_active_listing_was_soft_deleted_does_not_appear_on_the_home_page(): void
    {
        $category = Category::factory()->create(['name' => 'Anúncio Excluído']);
        $listing = Listing::factory()->create(['category_id' => $category->id, 'status' => ListingStatus::Ativo]);
        $listing->delete();

        $this->get('/')->assertOk()->assertDontSee('Anúncio Excluído');
    }

    public function test_inactive_category_does_not_appear_even_with_an_active_listing(): void
    {
        $category = Category::factory()->create(['name' => 'Categoria Inativa', 'is_active' => false]);
        Listing::factory()->create(['category_id' => $category->id, 'status' => ListingStatus::Ativo]);

        $this->get('/')->assertOk()->assertDontSee('Categoria Inativa');
    }

    public function test_home_shows_at_most_ten_categories(): void
    {
        Category::factory()->count(15)->create()->each(function (Category $category) {
            Listing::factory()->create(['category_id' => $category->id, 'status' => ListingStatus::Ativo]);
        });

        $categories = Livewire::test(Home::class)->viewData('categories');

        $this->assertCount(10, $categories);
    }
}
