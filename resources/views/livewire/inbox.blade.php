<div>
    <h1 class="text-xl font-bold text-gray-900 mb-6">Mensagens</h1>

    @if ($conversations->isEmpty())
        <div class="bg-white border border-gray-100 rounded-lg p-10 text-center text-gray-500">
            Nenhuma conversa por enquanto.
        </div>
    @else
        <div class="space-y-2">
            @foreach ($conversations as $conversation)
                @php
                    $isBuyer = $conversation->buyer_id === auth()->id();
                    $otherUser = $isBuyer ? $conversation->seller : $conversation->buyer;
                    $lastMessage = $conversation->messages->first();
                @endphp
                <a href="{{ route('messages.show', $conversation) }}" wire:navigate
                    class="block bg-white border border-gray-100 rounded-lg p-4 hover:border-orange-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $conversation->listing->title }}</p>
                            <p class="text-sm text-gray-500">Com {{ $otherUser->name ?? 'Usuário removido' }}</p>
                            @if ($lastMessage)
                                <p class="text-sm text-gray-400 truncate mt-1">{{ $lastMessage->body }}</p>
                            @endif
                        </div>
                        @if ($conversation->unread_count > 0)
                            <span class="bg-orange-600 text-white text-xs rounded-full px-2 py-1">{{ $conversation->unread_count }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
