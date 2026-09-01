<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ConversationShow extends Component
{
    public ?Conversation $conversation = null;

    public ?Listing $listing = null;

    public string $body = '';

    public function mount(?Conversation $conversation = null, ?Listing $listing = null): void
    {
        if ($conversation) {
            $this->authorize('view', $conversation);

            $this->conversation = $conversation;

            $conversation->messages()
                ->whereNull('read_at')
                ->where('sender_id', '!=', Auth::id())
                ->update(['read_at' => now()]);

            return;
        }

        abort_if(Auth::id() === $listing->user_id, 403);

        $existing = Conversation::query()
            ->where('listing_id', $listing->id)
            ->where('buyer_id', Auth::id())
            ->first();

        if ($existing) {
            $this->redirect(route('messages.show', $existing), navigate: true);

            return;
        }

        $this->listing = $listing;
    }

    public function send(): void
    {
        $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $isNewConversation = ! $this->conversation;

        if ($isNewConversation) {
            $this->conversation = Conversation::query()->firstOrCreate([
                'listing_id' => $this->listing->id,
                'buyer_id' => Auth::id(),
            ], [
                'seller_id' => $this->listing->user_id,
            ]);
        }

        $this->conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $this->body,
        ]);

        $this->conversation->touch();

        $this->body = '';

        $this->dispatch('message-sent');

        if ($isNewConversation) {
            $this->redirect(route('messages.show', $this->conversation), navigate: true);
        }
    }

    public function render()
    {
        if (! $this->conversation) {
            return view('livewire.conversation-show', [
                'messages' => collect(),
            ]);
        }

        $this->conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', Auth::id())
            ->update(['read_at' => now()]);

        return view('livewire.conversation-show', [
            'messages' => $this->conversation->messages()->with('sender')->orderBy('created_at')->get(),
        ]);
    }
}
