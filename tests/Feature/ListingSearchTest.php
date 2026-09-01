<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Livewire\ListingIndex;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListingSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_search_box_is_present_on_the_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('name="search"', false)
            ->assertSee('action="'.route('listings.index').'"', false);
    }

    public function test_search_with_a_single_word_matches_listings_containing_it(): void
    {
        $match = Listing::factory()->create(['status' => ListingStatus::Ativo, 'title' => 'Bicicleta aro 29']);
        $noMatch = Listing::factory()->create(['status' => ListingStatus::Ativo, 'title' => 'Mesa de escritório']);

        Livewire::test(ListingIndex::class)
            ->set('search', 'bicicleta')
            ->assertSee($match->title)
            ->assertDontSee($noMatch->title);
    }

    public function test_search_with_multiple_words_matches_listings_containing_any_of_them(): void
    {
        $matchesFirstWord = Listing::factory()->create(['status' => ListingStatus::Ativo, 'title' => 'Bicicleta aro 29']);
        $matchesSecondWord = Listing::factory()->create(['status' => ListingStatus::Ativo, 'title' => 'Mesa de escritório']);
        $matchesNeither = Listing::factory()->create(['status' => ListingStatus::Ativo, 'title' => 'Sofá de três lugares']);

        Livewire::test(ListingIndex::class)
            ->set('search', 'bicicleta mesa')
            ->assertSee($matchesFirstWord->title)
            ->assertSee($matchesSecondWord->title)
            ->assertDontSee($matchesNeither->title);
    }

    public function test_search_no_longer_auto_selects_a_category_matching_the_typed_term(): void
    {
        $category = Category::factory()->create(['name' => 'Eletrônicos']);

        $test = Livewire::withQueryParams(['search' => 'Eletrônicos'])
            ->test(ListingIndex::class);

        $test->assertSet('categoryId', null)
            ->assertSet('search', 'Eletrônicos');
    }

    public function test_search_no_longer_auto_selects_a_condition_or_state_matching_the_typed_term(): void
    {
        $test = Livewire::withQueryParams(['search' => 'cadeira usado SP'])
            ->test(ListingIndex::class);

        $test->assertSet('condition', null)
            ->assertSet('state', null)
            ->assertSet('search', 'cadeira usado SP');
    }

    public function test_search_with_no_matching_words_returns_no_listings(): void
    {
        $listing = Listing::factory()->create(['status' => ListingStatus::Ativo, 'title' => 'Bicicleta aro 29']);

        Livewire::test(ListingIndex::class)
            ->set('search', 'geladeira micro-ondas')
            ->assertDontSee($listing->title);
    }

    public function test_search_matches_a_word_found_only_in_the_description(): void
    {
        $match = Listing::factory()->create([
            'status' => ListingStatus::Ativo,
            'title' => 'Kit de ferramentas',
            'description' => 'Acompanha chave de fenda e alicate.',
        ]);
        $noMatch = Listing::factory()->create([
            'status' => ListingStatus::Ativo,
            'title' => 'Mesa de escritório',
            'description' => 'Em bom estado, sem arranhões.',
        ]);

        Livewire::test(ListingIndex::class)
            ->set('search', 'alicate')
            ->assertSee($match->title)
            ->assertDontSee($noMatch->title);
    }

    public function test_search_with_multiple_words_matches_across_title_and_description(): void
    {
        $matchesByTitle = Listing::factory()->create([
            'status' => ListingStatus::Ativo,
            'title' => 'Bicicleta aro 29',
            'description' => 'Usada, poucos km rodados.',
        ]);
        $matchesByDescription = Listing::factory()->create([
            'status' => ListingStatus::Ativo,
            'title' => 'Mesa de escritório',
            'description' => 'Acompanha cadeira giratória.',
        ]);
        $matchesNeither = Listing::factory()->create([
            'status' => ListingStatus::Ativo,
            'title' => 'Sofá de três lugares',
            'description' => 'Tecido impermeável.',
        ]);

        Livewire::test(ListingIndex::class)
            ->set('search', 'bicicleta giratória')
            ->assertSee($matchesByTitle->title)
            ->assertSee($matchesByDescription->title)
            ->assertDontSee($matchesNeither->title);
    }
}
