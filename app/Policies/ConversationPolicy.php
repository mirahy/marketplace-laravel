<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->buyer_id || $user->id === $conversation->seller_id;
    }

    public function update(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->buyer_id || $user->id === $conversation->seller_id;
    }
}
