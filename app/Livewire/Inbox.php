<?php

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Inbox extends Component
{
    public function render()
    {
        $userId = Auth::id();

        $conversations = Conversation::query()
            ->with(['listing', 'buyer', 'seller', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->whereNull('read_at')->where('sender_id', '!=', $userId);
            }])
            ->latest('updated_at')
            ->get();

        return view('livewire.inbox', [
            'conversations' => $conversations,
        ]);
    }
}
