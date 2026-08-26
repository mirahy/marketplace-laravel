<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Livewire\ConversationShow;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ConversationDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function makeConversation(): Conversation
    {
        $seller = User::factory()->anunciante()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id, 'status' => ListingStatus::Ativo]);

        return Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
    }

    public function test_message_time_is_displayed(): void
    {
        $conversation = $this->makeConversation();

        $message = $conversation->messages()->create([
            'sender_id' => $conversation->buyer_id,
            'body' => 'Olá, ainda está disponível?',
        ]);

        $this->actingAs($conversation->buyer)
            ->get("/mensagens/{$conversation->id}")
            ->assertOk()
            ->assertSee($message->created_at->format('H:i'));
    }

    public function test_date_separator_shows_weekday_name_for_message_within_current_week(): void
    {
        $conversation = $this->makeConversation();

        $message = $conversation->messages()->create([
            'sender_id' => $conversation->buyer_id,
            'body' => 'Mensagem de hoje',
        ]);

        $expectedLabel = Str::before(now()->locale('pt_BR')->translatedFormat('l'), '-feira');

        $this->actingAs($conversation->buyer)
            ->get("/mensagens/{$conversation->id}")
            ->assertOk()
            ->assertSee($expectedLabel)
            ->assertDontSee($message->created_at->format('d/m/Y'));
    }

    public function test_date_separator_shows_date_for_message_before_current_week(): void
    {
        $conversation = $this->makeConversation();

        $message = $conversation->messages()->create([
            'sender_id' => $conversation->buyer_id,
            'body' => 'Mensagem antiga',
        ]);
        $message->forceFill(['created_at' => now()->subWeeks(2)])->save();

        $this->actingAs($conversation->buyer)
            ->get("/mensagens/{$conversation->id}")
            ->assertOk()
            ->assertSee($message->created_at->format('d/m/Y'));
    }

    public function test_only_one_date_separator_is_shown_per_day(): void
    {
        $conversation = $this->makeConversation();

        $conversation->messages()->create(['sender_id' => $conversation->buyer_id, 'body' => 'Primeira mensagem de hoje']);
        $conversation->messages()->create(['sender_id' => $conversation->seller_id, 'body' => 'Segunda mensagem de hoje']);

        $expectedLabel = Str::before(now()->locale('pt_BR')->translatedFormat('l'), '-feira');

        $html = $this->actingAs($conversation->buyer)
            ->get("/mensagens/{$conversation->id}")
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, $expectedLabel));
    }

    public function test_sending_a_message_dispatches_the_message_sent_event(): void
    {
        $conversation = $this->makeConversation();

        Livewire::actingAs($conversation->buyer)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('body', 'Oi, tudo bem?')
            ->call('send')
            ->assertDispatched('message-sent');
    }
}
