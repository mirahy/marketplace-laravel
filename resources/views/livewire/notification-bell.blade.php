<div class="relative" x-data="{ open: false }">
    <button @click="open = ! open" class="relative text-slate-300 hover:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-orange-600 text-white text-[10px] leading-none rounded-full h-4 min-w-4 px-1 flex items-center justify-center">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak
        class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-20 border border-gray-100">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-900">Notificações</span>
            @if ($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-orange-600 hover:underline">Marcar todas como lidas</button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse ($notifications as $notification)
                <button wire:click="markAsRead('{{ $notification->id }}')"
                    class="w-full text-left block px-4 py-3 border-b border-gray-50 hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-orange-50/50' }}">
                    <p class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? '' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $notification->data['body'] ?? '' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </button>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">Nenhuma notificação por enquanto.</p>
            @endforelse
        </div>
    </div>
</div>
