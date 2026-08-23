<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Notifications\NewMessageReceived;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ConversationShow extends Component
{
    public Conversation $conversation;

    public string $body = '';

    public function mount(Conversation $conversation): void
    {
        $this->authorize('view', $conversation);

        $this->conversation = $conversation;

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', Auth::id())
            ->update(['read_at' => now()]);
    }

    public function send(): void
    {
        $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $this->body,
        ]);

        $this->conversation->touch();

        $recipient = $message->sender_id === $this->conversation->buyer_id
            ? $this->conversation->seller
            : $this->conversation->buyer;

        if ($recipient) {
            Notification::send($recipient, new NewMessageReceived($message, $this->conversation));
        }

        $this->body = '';
    }

    public function render()
    {
        $this->conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', Auth::id())
            ->update(['read_at' => now()]);

        return view('livewire.conversation-show', [
            'messages' => $this->conversation->messages()->with('sender')->orderBy('created_at')->get(),
        ]);
    }
}
