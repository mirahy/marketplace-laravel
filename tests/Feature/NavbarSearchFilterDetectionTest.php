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

    public function test_search_query_string_with_category_name_sets_the_category_filter_and_clears_the_search_text(): void
    {
        $category = Category::factory()->create(['name' => 'Eletrônicos']);

        $test = Livewire::withQueryParams(['search' => 'Eletronicos'])
            ->test(ListingIndex::class);

        $test->assertSet('categoryId', $category->id)
            ->assertSet('search', '');
    }

    public function test_search_query_string_with_condition_word_sets_the_condition_filter_and_strips_it_from_the_search_text(): void
    {
        $test = Livewire::withQueryParams(['search' => 'cadeira usado'])
            ->test(ListingIndex::class);

        $test->assertSet('condition', ListingCondition::Usado->value)
            ->assertSet('search', 'cadeira');
    }

    public function test_search_query_string_with_state_code_sets_the_state_filter_and_strips_it_from_the_search_text(): void
    {
        $test = Livewire::withQueryParams(['search' => 'cadeira SP'])
            ->test(ListingIndex::class);

        $test->assertSet('state', 'SP')
            ->assertSet('search', 'cadeira');
    }

    public function test_search_query_string_with_full_state_name_sets_the_state_filter_and_strips_it_from_the_search_text(): void
    {
        $test = Livewire::withQueryParams(['search' => 'cadeira em São Paulo'])
            ->test(ListingIndex::class);

        $test->assertSet('state', 'SP')
            ->assertSet('search', 'cadeira em');
    }

    public function test_search_term_combining_category_condition_and_state_strips_all_three_leaving_only_free_text(): void
    {
        $category = Category::factory()->create(['name' => 'Eletrônicos']);

        $test = Livewire::withQueryParams(['search' => 'fone Eletrônicos usado SP'])
            ->test(ListingIndex::class);

        $test->assertSet('categoryId', $category->id)
            ->assertSet('condition', ListingCondition::Usado->value)
            ->assertSet('state', 'SP')
            ->assertSet('search', 'fone');
    }

    public function test_search_term_without_any_match_does_not_set_extra_filters_and_keeps_the_search_text(): void
    {
        $test = Livewire::withQueryParams(['search' => 'alguma busca qualquer'])
            ->test(ListingIndex::class);

        $test->assertSet('categoryId', null)
            ->assertSet('condition', null)
            ->assertSet('state', null)
            ->assertSet('search', 'alguma busca qualquer');
    }

    public function test_explicit_category_id_is_not_overridden_by_search_term_detection_and_the_search_text_is_untouched(): void
    {
        $mentioned = Category::factory()->create(['name' => 'Eletrônicos']);
        $explicit = Category::factory()->create(['name' => 'Móveis']);

        $test = Livewire::withQueryParams(['search' => 'Eletronicos', 'categoryId' => $explicit->id])
            ->test(ListingIndex::class);

        $test->assertSet('categoryId', $explicit->id)
            ->assertSet('search', 'Eletronicos');
    }
}
