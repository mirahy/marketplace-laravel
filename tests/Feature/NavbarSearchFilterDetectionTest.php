<?php

namespace Tests\Feature;

use App\Enums\ListingCondition;
use App\Livewire\ListingIndex;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NavbarSearchFilterDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_search_box_is_present_on_the_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('name="search"', false)
            ->assertSee('action="'.route('listings.index').'"', false);
    }

    public function test_search_query_string_with_category_name_sets_the_category_filter(): void
    {
        $category = Category::factory()->create(['name' => 'Eletrônicos']);

        $test = Livewire::withQueryParams(['search' => 'Eletronicos'])
            ->test(ListingIndex::class);

        $test->assertSet('categoryId', $category->id);
    }

    public function test_search_query_string_with_condition_word_sets_the_condition_filter(): void
    {
        $test = Livewire::withQueryParams(['search' => 'usado'])
            ->test(ListingIndex::class);

        $test->assertSet('condition', ListingCondition::Usado->value);
    }

    public function test_search_query_string_with_state_code_sets_the_state_filter(): void
    {
        $test = Livewire::withQueryParams(['search' => 'SP'])
            ->test(ListingIndex::class);

        $test->assertSet('state', 'SP');
    }

    public function test_search_query_string_with_full_state_name_sets_the_state_filter(): void
    {
        $test = Livewire::withQueryParams(['search' => 'cadeira em São Paulo'])
            ->test(ListingIndex::class);

        $test->assertSet('state', 'SP');
    }

    public function test_search_term_without_any_match_does_not_set_extra_filters(): void
    {
        $test = Livewire::withQueryParams(['search' => 'alguma busca qualquer'])
            ->test(ListingIndex::class);

        $test->assertSet('categoryId', null)
            ->assertSet('condition', null)
            ->assertSet('state', null);
    }

    public function test_explicit_category_id_is_not_overridden_by_search_term_detection(): void
    {
        $mentioned = Category::factory()->create(['name' => 'Eletrônicos']);
        $explicit = Category::factory()->create(['name' => 'Móveis']);

        $test = Livewire::withQueryParams(['search' => 'Eletronicos', 'categoryId' => $explicit->id])
            ->test(ListingIndex::class);

        $test->assertSet('categoryId', $explicit->id);
    }
}
