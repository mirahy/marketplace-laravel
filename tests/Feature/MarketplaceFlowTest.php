<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use App\Livewire\ConversationShow;
use App\Livewire\ListingForm;
use App\Livewire\ListingShow;
use App\Notifications\NewMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MarketplaceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_public_pages(): void
    {
        $category = Category::factory()->create();
        Listing::factory()->create(['category_id' => $category->id, 'status' => ListingStatus::Ativo]);

        $this->get('/')->assertOk();
        $this->get('/anuncios')->assertOk();
    }

    public function test_user_can_create_listing_with_photos_and_it_starts_pending_review(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($user)
            ->test(ListingForm::class)
            ->set('title', 'Bicicleta aro 29')
            ->set('description', 'Pouco uso, muito bem conservada.')
            ->set('price', 899.90)
            ->set('condition', 'usado')
            ->set('categoryId', $category->id)
            ->set('city', 'Curitiba')
            ->set('state', 'PR')
            ->set('newPhotos', [UploadedFile::fake()->image('bike.jpg')])
            ->call('save')
            ->assertRedirect(route('listings.mine'));

        $listing = Listing::query()->where('title', 'Bicicleta aro 29')->firstOrFail();

        $this->assertSame(ListingStatus::EmAnalise, $listing->status);
        $this->assertSame('PR', $listing->state);
        $this->assertCount(1, $listing->images);

        // Pending listings are not visible on the public index yet.
        $this->get('/anuncios')->assertOk()->assertDontSee('Bicicleta aro 29');

        // Simulate admin approval (same effect as the Filament "Aprovar" action).
        $listing->update(['status' => ListingStatus::Ativo, 'published_at' => now()]);

        $this->get('/anuncios')->assertOk()->assertSee('Bicicleta aro 29');
    }

    public function test_listing_form_rejects_an_invalid_state_code(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($user)
            ->test(ListingForm::class)
            ->set('title', 'Sofá')
            ->set('description', 'Em bom estado.')
            ->set('price', 100)
            ->set('condition', 'usado')
            ->set('categoryId', $category->id)
            ->set('city', 'Curitiba')
            ->set('state', 'XX')
            ->call('save')
            ->assertHasErrors(['state']);
    }

    public function test_create_and_edit_listing_routes_are_reachable_over_http(): void
    {
        // Regression test: /anuncios/novo and /anuncios/{slug}/editar must not be
        // shadowed by the /anuncios/{listing:slug} wildcard route.
        $user = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get('/anuncios/novo')->assertOk();
        $this->actingAs($user)->get("/anuncios/{$listing->slug}/editar")->assertOk();
    }

    public function test_create_listing_route_requires_authentication(): void
    {
        $this->get('/anuncios/novo')->assertRedirect(route('login'));
    }

    public function test_only_the_owner_can_edit_or_delete_their_listing(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($stranger->cant('update', $listing));
        $this->assertTrue($stranger->cant('delete', $listing));
        $this->assertTrue($owner->can('update', $listing));
        $this->assertTrue($owner->can('delete', $listing));
    }

    public function test_admin_can_access_panel_and_regular_user_cannot(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_buyer_and_seller_can_message_each_other_and_strangers_are_blocked(): void
    {
        Notification::fake();

        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

        Livewire::actingAs($buyer)
            ->test(ListingShow::class, ['listing' => $listing])
            ->call('sendMessage');

        $conversation = Conversation::query()->firstOrFail();
        $this->assertSame($buyer->id, $conversation->buyer_id);
        $this->assertSame($seller->id, $conversation->seller_id);

        Livewire::actingAs($seller)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('body', 'Ainda está disponível?')
            ->call('send');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $seller->id,
            'body' => 'Ainda está disponível?',
        ]);

        // The seller sent the message, so the buyer is the one notified by e-mail.
        Notification::assertSentTo($buyer, NewMessageReceived::class);
        Notification::assertNotSentTo($seller, NewMessageReceived::class);

        $this->actingAs($stranger)
            ->get("/mensagens/{$conversation->id}")
            ->assertForbidden();
    }
}
