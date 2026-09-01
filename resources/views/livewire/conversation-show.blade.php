@php
    $isBuyer = $conversation && $conversation->buyer_id === auth()->id();
    $otherUser = $conversation
        ? ($isBuyer ? $conversation->seller : $conversation->buyer)
        : $listing->user;
    $lastMessageDate = null;
@endphp

<div wire:poll.5s class="max-w-2xl mx-auto"
    x-init="
        $nextTick(() => { $refs.messages.scrollTop = $refs.messages.scrollHeight; });
        $wire.on('message-sent', () => {
            $nextTick(() => { $refs.messages.scrollTop = $refs.messages.scrollHeight; });
        });
    "
>
    <x-breadcrumbs :items="[
        ['label' => __('Início'), 'url' => route('home')],
        ['label' => __('Mensagens'), 'url' => route('messages.index')],
        ['label' => $otherUser->name ?? __('Conversa')],
    ]" />

    <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <p class="font-medium text-gray-900">{{ ($conversation ? $conversation->listing?->title : $listing->title) ?? __('Anúncio removido') }}</p>
            <p class="text-sm text-gray-500">{{ __('Com :nome', ['nome' => $otherUser->name ?? __('Usuário removido')]) }}</p>
        </div>

        <div x-ref="messages" class="p-4 space-y-3 max-h-96 overflow-y-auto">
            @forelse ($messages as $message)
                @php
                    $isMine = $message->sender_id === auth()->id();
                    $messageDate = $message->created_at->copy()->startOfDay();
                    $isNewDay = ! $lastMessageDate || ! $messageDate->equalTo($lastMessageDate);
                    $lastMessageDate = $messageDate;
                @endphp

                @if ($isNewDay)
                    @php
                        $weekStart = now()->startOfWeek(\Carbon\Carbon::SUNDAY);
                        $weekEnd = now()->endOfWeek(\Carbon\Carbon::SUNDAY);
                        $dateLabel = $message->created_at->between($weekStart, $weekEnd)
                            ? \Illuminate\Support\Str::before($message->created_at->locale('pt_BR')->translatedFormat('l'), '-feira')
                            : $message->created_at->format('d/m/Y');
                    @endphp
                    <div wire:key="date-{{ $message->id }}" class="flex justify-center my-3">
                        <span class="text-xs font-medium text-gray-400 bg-gray-100 rounded-full px-3 py-1 capitalize">
                            {{ $dateLabel }}
                        </span>
                    </div>
                @endif

                <div wire:key="message-{{ $message->id }}" class="{{ $isMine ? 'text-right' : 'text-left' }}">
                    <span class="inline-block px-3 py-2 rounded-lg text-sm {{ $isMine ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                        <span class="block whitespace-pre-line">{{ $message->body }}</span>
                        <span class="block text-right text-[10px] mt-1 {{ $isMine ? 'text-orange-100' : 'text-gray-400' }}">
                            {{ $message->created_at->format('H:i') }}
                        </span>
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center">{{ __('Nenhuma mensagem ainda. Diga olá!') }}</p>
            @endforelse
        </div>

        <form wire:submit="send" class="p-4 border-t border-gray-100 flex gap-2">
            <input type="text" wire:model="body" placeholder="{{ __('Escreva uma mensagem...') }}"
                class="flex-1 rounded-md border-gray-300 text-sm">
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md font-semibold text-sm hover:bg-orange-700">
                {{ __('Enviar') }}
            </button>
        </form>
        @error('body') <p class="text-sm text-red-600 px-4 pb-3">{{ $message }}</p> @enderror
    </div>
</div>
