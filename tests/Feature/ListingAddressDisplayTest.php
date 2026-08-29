<?php

namespace Tests\Feature;

use App\Enums\AddressType;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingAddressDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_address_fields_produce_the_three_expected_lines(): void
    {
        $listing = Listing::factory()->create([
            'address_type' => AddressType::Rua,
            'address_street' => 'das Flores',
            'address_number' => '123',
            'address_neighborhood' => 'Centro',
            'address_complement' => 'Apto 12',
            'city' => 'Naviraí',
            'state' => 'MS',
        ]);

        $this->assertSame([
            'Rua das Flores, 123',
            'Centro - Naviraí/MS',
            'Apto 12',
        ], $listing->addressLines());
    }

    public function test_only_address_type_without_street_or_number_shows_just_the_type(): void
    {
        $listing = Listing::factory()->create([
            'address_type' => AddressType::Rua,
            'address_street' => null,
            'address_number' => null,
            'address_neighborhood' => null,
            'address_complement' => null,
            'city' => '',
            'state' => '',
        ]);

        $this->assertSame(['Rua'], $listing->addressLines());
    }

    public function test_only_number_without_type_or_street_shows_just_the_number_without_leading_comma(): void
    {
        $listing = Listing::factory()->create([
            'address_type' => null,
            'address_street' => null,
            'address_number' => '45',
            'address_neighborhood' => null,
            'address_complement' => null,
            'city' => '',
            'state' => '',
        ]);

        $this->assertSame(['45'], $listing->addressLines());
    }

    public function test_only_neighborhood_without_city_or_state_shows_just_the_neighborhood(): void
    {
        $listing = Listing::factory()->create([
            'address_type' => null,
            'address_street' => null,
            'address_number' => null,
            'address_neighborhood' => 'Centro',
            'address_complement' => null,
            'city' => '',
            'state' => '',
        ]);

        $this->assertSame(['Centro'], $listing->addressLines());
    }

    public function test_only_city_without_state_or_neighborhood_shows_just_the_city(): void
    {
        $listing = Listing::factory()->create([
            'address_type' => null,
            'address_street' => null,
            'address_number' => null,
            'address_neighborhood' => null,
            'address_complement' => null,
            'city' => 'Naviraí',
            'state' => '',
        ]);

        $this->assertSame(['Naviraí'], $listing->addressLines());
    }

    public function test_no_address_fields_at_all_produces_no_lines(): void
    {
        $listing = Listing::factory()->create([
            'address_type' => null,
            'address_street' => null,
            'address_number' => null,
            'address_neighborhood' => null,
            'address_complement' => null,
            'city' => '',
            'state' => '',
        ]);

        $this->assertSame([], $listing->addressLines());
    }

    public function test_complement_alone_produces_a_single_line(): void
    {
        $listing = Listing::factory()->create([
            'address_type' => null,
            'address_street' => null,
            'address_number' => null,
            'address_neighborhood' => null,
            'address_complement' => 'Fundos',
            'city' => '',
            'state' => '',
        ]);

        $this->assertSame(['Fundos'], $listing->addressLines());
    }

    public function test_listing_page_shows_address_lines_and_no_longer_shows_city_state_next_to_condition(): void
    {
        $seller = User::factory()->create();
        $category = Category::factory()->create();
        $listing = Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'address_type' => AddressType::Rua,
            'address_street' => 'das Flores',
            'address_number' => '123',
            'address_neighborhood' => 'Centro',
            'address_complement' => 'Apto 12',
            'city' => 'Naviraí',
            'state' => 'MS',
        ]);

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()
            ->assertSee('Rua das Flores, 123')
            ->assertSee('Centro - Naviraí/MS')
            ->assertSee('Apto 12')
            ->assertDontSee($listing->condition->getLabel().' · ');
    }
}
