<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreadcrumbsTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_shows_the_category_name_in_the_breadcrumb(): void
    {
        $category = Category::factory()->create(['name' => 'Eletrônicos']);

        $response = $this->get('/categorias/'.$category->slug);

        $response->assertOk()->assertSee('Eletrônicos');
    }

    public function test_listing_page_shows_category_and_title_in_the_breadcrumb(): void
    {
        $category = Category::factory()->create(['name' => 'Móveis']);
        $listing = Listing::factory()->create(['category_id' => $category->id, 'title' => 'Sofá de canto']);

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()->assertSee('Móveis')->assertSee('Sofá de canto');
    }

    public function test_my_listings_page_shows_breadcrumb(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/meus-anuncios');

        $response->assertOk()->assertSee('Meus anúncios');
    }

    public function test_conversation_page_shows_the_other_participants_name_in_the_breadcrumb(): void
    {
        $seller = User::factory()->create(['name' => 'Vendedor Teste']);
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id]);
        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $response = $this->actingAs($buyer)->get('/mensagens/'.$conversation->id);

        $response->assertOk()->assertSee('Vendedor Teste');
    }
}
