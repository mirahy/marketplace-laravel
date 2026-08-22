<?php

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
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

        $this->conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $this->body,
        ]);

        $this->conversation->touch();
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
