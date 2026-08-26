@props(['items'])

<nav aria-label="Breadcrumb" class="mb-4 text-sm">
    <ol class="flex flex-wrap items-center gap-1.5 text-gray-500">
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                @if (! $loop->first)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                @endif
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" wire:navigate class="hover:text-orange-600 transition-colors">{{ $item['label'] }}</a>
                @else
                    <span class="{{ $loop->last ? 'text-gray-700 font-medium truncate max-w-[200px] sm:max-w-xs' : '' }}">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
