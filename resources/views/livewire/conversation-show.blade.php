@php
    $isBuyer = $conversation->buyer_id === auth()->id();
    $otherUser = $isBuyer ? $conversation->seller : $conversation->buyer;
@endphp

<div wire:poll.5s class="max-w-2xl mx-auto">
    <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <p class="font-medium text-gray-900">{{ $conversation->listing->title }}</p>
            <p class="text-sm text-gray-500">Com {{ $otherUser->name }}</p>
        </div>

        <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
            @forelse ($messages as $message)
                <div class="{{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }}">
                    <span class="inline-block px-3 py-2 rounded-lg text-sm {{ $message->sender_id === auth()->id() ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                        {{ $message->body }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center">Nenhuma mensagem ainda. Diga olá!</p>
            @endforelse
        </div>

        <form wire:submit="send" class="p-4 border-t border-gray-100 flex gap-2">
            <input type="text" wire:model="body" placeholder="Escreva uma mensagem..."
                class="flex-1 rounded-md border-gray-300 text-sm">
            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-md font-semibold text-sm hover:bg-amber-700">
                Enviar
            </button>
        </form>
        @error('body') <p class="text-sm text-red-600 px-4 pb-3">{{ $message }}</p> @enderror
    </div>
</div>
