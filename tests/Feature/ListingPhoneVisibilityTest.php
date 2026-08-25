<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingPhoneVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_never_sees_the_sellers_phone_number(): void
    {
        $seller = User::factory()->create(['phone' => '(67) 91234-5678', 'show_phone' => true]);
        $listing = Listing::factory()->create(['user_id' => $seller->id]);

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()->assertDontSee('(67) 91234-5678');
    }

    public function test_sellers_phone_is_hidden_when_show_phone_is_disabled(): void
    {
        $seller = User::factory()->create(['phone' => '(67) 91234-5678', 'show_phone' => false]);
        $listing = Listing::factory()->create(['user_id' => $seller->id]);

        $response = $this->get('/anuncios/'.$listing->slug);

        $response->assertOk()->assertDontSee('(67) 91234-5678');
    }

    public function test_authenticated_viewer_sees_the_sellers_phone_when_show_phone_is_enabled(): void
    {
        $seller = User::factory()->create(['phone' => '(67) 91234-5678', 'show_phone' => true]);
        $viewer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($viewer)->get('/anuncios/'.$listing->slug);

        $response->assertOk()->assertSee('(67) 91234-5678');
    }

    public function test_authenticated_viewer_does_not_see_the_sellers_phone_when_show_phone_is_disabled(): void
    {
        $seller = User::factory()->create(['phone' => '(67) 91234-5678', 'show_phone' => false]);
        $viewer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($viewer)->get('/anuncios/'.$listing->slug);

        $response->assertOk()->assertDontSee('(67) 91234-5678');
    }
}
