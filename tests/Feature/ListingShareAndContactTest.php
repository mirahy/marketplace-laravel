<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;
use Tests\TestCase;

class ListingShareAndContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_page_has_open_graph_tags_with_title_condition_and_price(): void
    {
        $listing = Listing::factory()->create(['title' => 'Bicicleta aro 29', 'price' => 850, 'condition' => 'usado']);

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()
            ->assertSee('<meta property="og:title" content="Bicicleta aro 29">', false)
            ->assertSee('property="og:description" content="'.$listing->condition->getLabel().' · '.Number::currency(850, in: 'BRL'), false)
            ->assertSee('<meta property="og:url" content="'.route('listings.show', $listing).'">', false);
    }

    public function test_open_graph_image_uses_the_listings_first_photo(): void
    {
        $listing = Listing::factory()->create();
        ListingImage::factory()->create(['listing_id' => $listing->id, 'order' => 0, 'path' => 'https://picsum.photos/seed/first/800/600']);
        ListingImage::factory()->create(['listing_id' => $listing->id, 'order' => 1, 'path' => 'https://picsum.photos/seed/second/800/600']);

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()->assertSee('<meta property="og:image" content="https://picsum.photos/seed/first/800/600">', false);
    }

    public function test_open_graph_image_falls_back_to_the_site_logo_without_photos(): void
    {
        $listing = Listing::factory()->create();

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()->assertSee('<meta property="og:image" content="'.asset('img/adb.png').'">', false);
    }

    public function test_whatsapp_link_includes_a_prefilled_message_with_the_listing_url(): void
    {
        $seller = User::factory()->create(['phone' => '(67) 91234-5678', 'show_phone' => true, 'show_phone_to_guests' => true]);
        $listing = Listing::factory()->create(['user_id' => $seller->id]);

        $expectedMessage = urlencode(__('Olá, gostaria de falar sobre o anúncio: :link', ['link' => route('listings.show', $listing)]));

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()->assertSee('https://wa.me/5567912345678?text='.$expectedMessage, false);
    }

    public function test_phone_number_is_wrapped_in_a_copy_to_clipboard_button(): void
    {
        $seller = User::factory()->create(['phone' => '(67) 91234-5678', 'show_phone' => true, 'show_phone_to_guests' => true]);
        $listing = Listing::factory()->create(['user_id' => $seller->id]);

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()
            ->assertSee("navigator.clipboard.writeText('(67) 91234-5678')", false)
            ->assertSee(__('Copiado!'));
    }
}
