<?php

namespace Tests\Feature;

use App\Livewire\MyListings;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyListingsPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_listings_page_paginates_at_ten_items(): void
    {
        $user = User::factory()->anunciante()->create();
        Listing::factory()->count(15)->create(['user_id' => $user->id]);

        $component = Livewire::actingAs($user)->test(MyListings::class);

        $this->assertCount(10, $component->viewData('listings'));
        $this->assertSame(15, $component->viewData('listings')->total());
        $this->assertSame(2, $component->viewData('listings')->lastPage());
    }

    public function test_second_page_shows_the_remaining_listings(): void
    {
        $user = User::factory()->anunciante()->create();
        Listing::factory()->count(15)->create(['user_id' => $user->id]);

        $component = Livewire::actingAs($user)
            ->test(MyListings::class)
            ->call('gotoPage', 2);

        $this->assertCount(5, $component->viewData('listings'));
    }

    public function test_pagination_links_are_not_shown_when_everything_fits_on_one_page(): void
    {
        $user = User::factory()->anunciante()->create();
        Listing::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/meus-anuncios')
            ->assertOk()
            ->assertDontSee('gotoPage');
    }
}
