<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Livewire\ConversationShow;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\ListingStatusUpdated;
use App\Notifications\NewMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class SoftDeletedRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_page_still_works_when_the_seller_was_soft_deleted(): void
    {
        $seller = User::factory()->create(['name' => 'Vendedor Excluído']);
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

        $seller->delete();

        $this->get('/anuncios/'.$listing->slug)
            ->assertOk()
            ->assertSee('Vendedor Excluído');
    }

    public function test_approving_a_listing_does_not_crash_when_the_seller_was_soft_deleted(): void
    {
        Notification::fake();

        $seller = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::EmAnalise]);

        $seller->delete();

        $listing->approve();

        $this->assertSame(ListingStatus::Ativo, $listing->fresh()->status);
        Notification::assertSentTo($seller, ListingStatusUpdated::class);
    }

    public function test_conversation_page_still_works_when_the_other_party_was_soft_deleted(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create(['name' => 'Vendedor da Conversa']);
        $listing = Listing::factory()->create(['user_id' => $seller->id]);
        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $seller->delete();

        $this->actingAs($buyer)
            ->get('/mensagens/'.$conversation->id)
            ->assertOk()
            ->assertSee('Vendedor da Conversa');
    }

    public function test_sending_a_message_does_not_crash_when_the_recipient_was_soft_deleted(): void
    {
        Notification::fake();

        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id]);
        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $seller->delete();

        Livewire::actingAs($buyer)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('body', 'Ainda está disponível?')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'body' => 'Ainda está disponível?',
        ]);
        Notification::assertSentTo($seller, NewMessageReceived::class);
    }
}
