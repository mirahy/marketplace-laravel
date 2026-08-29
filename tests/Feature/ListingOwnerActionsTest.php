<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Livewire\ListingShow;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListingOwnerActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_pause_mark_as_sold_and_edit_buttons_on_an_active_listing(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id, 'status' => ListingStatus::Ativo]);

        $response = $this->actingAs($owner)->get('/anuncios/'.$listing->slug);

        $response->assertOk()
            ->assertSee('Pausar')
            ->assertSee('Marcar vendido')
            ->assertSee('Editar')
            ->assertDontSee('Enviar mensagem');
    }

    public function test_non_owner_does_not_see_owner_action_buttons(): void
    {
        $owner = User::factory()->anunciante()->create();
        $viewer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id, 'status' => ListingStatus::Ativo]);

        $response = $this->actingAs($viewer)->get('/anuncios/'.$listing->slug);

        $response->assertOk()
            ->assertDontSee('Pausar')
            ->assertDontSee('Marcar vendido')
            ->assertSee('Enviar mensagem');
    }

    public function test_owner_does_not_see_pause_or_mark_as_sold_when_listing_is_not_active(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id, 'status' => ListingStatus::Pausado]);

        $response = $this->actingAs($owner)->get('/anuncios/'.$listing->slug);

        $response->assertOk()
            ->assertDontSee('Pausar')
            ->assertDontSee('Marcar vendido')
            ->assertSee('Editar');
    }

    public function test_owner_can_pause_an_active_listing_via_the_listing_page(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id, 'status' => ListingStatus::Ativo]);

        Livewire::actingAs($owner)
            ->test(ListingShow::class, ['listing' => $listing])
            ->call('pause');

        $this->assertSame(ListingStatus::Pausado, $listing->fresh()->status);
    }

    public function test_owner_can_mark_an_active_listing_as_sold_via_the_listing_page(): void
    {
        $owner = User::factory()->anunciante()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id, 'status' => ListingStatus::Ativo]);

        Livewire::actingAs($owner)
            ->test(ListingShow::class, ['listing' => $listing])
            ->call('markAsSold');

        $this->assertSame(ListingStatus::Vendido, $listing->fresh()->status);
    }

    public function test_a_stranger_cannot_pause_or_mark_as_sold_someone_elses_listing(): void
    {
        $owner = User::factory()->anunciante()->create();
        $stranger = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id, 'status' => ListingStatus::Ativo]);

        Livewire::actingAs($stranger)
            ->test(ListingShow::class, ['listing' => $listing])
            ->call('pause')
            ->assertForbidden();

        $this->assertSame(ListingStatus::Ativo, $listing->fresh()->status);
    }
}
