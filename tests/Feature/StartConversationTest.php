<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Livewire\ConversationShow;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StartConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_the_start_conversation_page_does_not_create_a_conversation(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

        $this->actingAs($buyer)
            ->get('/mensagens/nova/'.$listing->slug)
            ->assertOk();

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_sending_the_first_message_creates_the_conversation(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

        Livewire::actingAs($buyer)
            ->test(ConversationShow::class, ['listing' => $listing])
            ->set('body', 'Olá!')
            ->call('send')
            ->assertRedirect();

        $this->assertDatabaseCount('conversations', 1);

        $conversation = Conversation::query()->firstOrFail();
        $this->assertSame($buyer->id, $conversation->buyer_id);
        $this->assertSame($seller->id, $conversation->seller_id);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'Olá!',
        ]);
    }

    public function test_listing_owner_cannot_start_a_conversation_with_themselves(): void
    {
        $seller = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

        $this->actingAs($seller)
            ->get('/mensagens/nova/'.$listing->slug)
            ->assertForbidden();
    }

    public function test_revisiting_the_start_page_redirects_to_the_existing_conversation(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($buyer)
            ->get('/mensagens/nova/'.$listing->slug)
            ->assertRedirect(route('messages.show', $conversation));

        // No second conversation should have been created for the same pair.
        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_sending_a_message_when_an_existing_conversation_already_exists_reuses_it(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

        $existing = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $existing->messages()->create(['sender_id' => $buyer->id, 'body' => 'Primeira mensagem']);

        Livewire::actingAs($buyer)
            ->test(ConversationShow::class, ['conversation' => $existing])
            ->set('body', 'Segunda mensagem')
            ->call('send');

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $existing->id,
            'body' => 'Segunda mensagem',
        ]);
    }
}
