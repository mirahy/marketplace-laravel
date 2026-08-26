<?php

namespace Tests\Feature;

use App\Livewire\ConversationShow;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\NewMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class BatchedMessageNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function makeConversation(): Conversation
    {
        $seller = User::factory()->anunciante()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->create(['user_id' => $seller->id]);

        return Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
    }

    private function sendMessage(Conversation $conversation, User $sender, string $body): void
    {
        Livewire::actingAs($sender)
            ->test(ConversationShow::class, ['conversation' => $conversation])
            ->set('body', $body)
            ->call('send')
            ->assertHasNoErrors();
    }

    public function test_sending_a_message_does_not_send_an_email_immediately(): void
    {
        Notification::fake();

        $conversation = $this->makeConversation();

        $this->sendMessage($conversation, $conversation->buyer, 'Ainda está disponível?');

        Notification::assertNothingSent();
    }

    public function test_batch_command_sends_email_to_the_recipient_and_marks_messages_as_notified(): void
    {
        Notification::fake();

        $conversation = $this->makeConversation();
        $this->sendMessage($conversation, $conversation->buyer, 'Ainda está disponível?');

        $this->artisan('messages:notify')->assertSuccessful();

        Notification::assertSentTo($conversation->seller, NewMessageReceived::class);

        $this->assertNotNull($conversation->messages()->first()->fresh()->email_notified_at);
    }

    public function test_email_body_contains_the_expected_text(): void
    {
        $conversation = $this->makeConversation();
        $message = $conversation->messages()->create([
            'sender_id' => $conversation->buyer_id,
            'body' => 'Ainda está disponível?',
        ]);

        $notification = new NewMessageReceived(
            $conversation->messages()->where('id', $message->id)->get(),
            $conversation
        );

        $mail = $notification->toMail($conversation->seller);

        $this->assertContains(
            'Você tem uma nova mensagem de '.$conversation->buyer->name.'.',
            $mail->introLines
        );
    }

    public function test_running_the_batch_command_twice_does_not_send_a_second_email(): void
    {
        Notification::fake();

        $conversation = $this->makeConversation();
        $this->sendMessage($conversation, $conversation->buyer, 'Ainda está disponível?');

        $this->artisan('messages:notify')->assertSuccessful();
        Notification::assertSentToTimes($conversation->seller, NewMessageReceived::class, 1);

        $this->artisan('messages:notify')->assertSuccessful();
        Notification::assertSentToTimes($conversation->seller, NewMessageReceived::class, 1);
    }

    public function test_each_conversation_generates_its_own_email(): void
    {
        Notification::fake();

        $buyer = User::factory()->create();

        $sellerA = User::factory()->anunciante()->create();
        $listingA = Listing::factory()->create(['user_id' => $sellerA->id]);
        $conversationA = Conversation::create([
            'listing_id' => $listingA->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $sellerA->id,
        ]);

        $sellerB = User::factory()->anunciante()->create();
        $listingB = Listing::factory()->create(['user_id' => $sellerB->id]);
        $conversationB = Conversation::create([
            'listing_id' => $listingB->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $sellerB->id,
        ]);

        $this->sendMessage($conversationA, $buyer, 'Mensagem pra conversa A');
        $this->sendMessage($conversationB, $buyer, 'Mensagem pra conversa B');

        $this->artisan('messages:notify')->assertSuccessful();

        Notification::assertSentToTimes($sellerA, NewMessageReceived::class, 1);
        Notification::assertSentToTimes($sellerB, NewMessageReceived::class, 1);
    }

    public function test_batch_command_does_not_crash_when_the_recipient_was_soft_deleted(): void
    {
        Notification::fake();

        $conversation = $this->makeConversation();
        $this->sendMessage($conversation, $conversation->buyer, 'Ainda está disponível?');

        $conversation->seller->delete();

        $this->artisan('messages:notify')->assertSuccessful();

        Notification::assertSentTo($conversation->seller, NewMessageReceived::class);
    }
}
