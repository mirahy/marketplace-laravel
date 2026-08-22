@props(['listing'])
@use('Illuminate\Support\Str')
@use('Illuminate\Support\Facades\Storage')
@use('Illuminate\Support\Number')

<a href="{{ route('listings.show', $listing) }}" wire:navigate
    class="block bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
    <div class="aspect-square bg-gray-100">
        @if ($listing->images->first())
            <img src="{{ Str::startsWith($listing->images->first()->path, 'http') ? $listing->images->first()->path : Storage::url($listing->images->first()->path) }}"
                alt="{{ $listing->title }}" class="w-full h-full object-cover">
        @endif
    </div>
    <div class="p-3">
        <p class="text-sm text-gray-800 font-medium truncate">{{ $listing->title }}</p>
        <p class="text-lg font-bold text-gray-900 mt-1">
            {{ Number::currency($listing->price, in: 'BRL') }}
        </p>
        <p class="text-xs text-gray-500 mt-1">{{ $listing->city }}/{{ $listing->state }}</p>
    </div>
</a>
