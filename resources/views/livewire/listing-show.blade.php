@use('Illuminate\Support\Facades\Storage')
@use('Illuminate\Support\Str')
@use('Illuminate\Support\Number')

<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @forelse ($listing->images as $image)
                    <img src="{{ Str::startsWith($image->path, 'http') ? $image->path : Storage::url($image->path) }}"
                        class="w-full h-64 object-cover rounded-lg bg-gray-100" alt="{{ $listing->title }}">
                @empty
                    <div class="w-full h-64 rounded-lg bg-gray-100"></div>
                @endforelse
            </div>

            <div class="mt-6 bg-white border border-gray-100 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-2">Descrição</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $listing->description }}</p>
            </div>
        </div>

        <div>
            <div class="bg-white border border-gray-100 rounded-lg p-6 sticky top-4">
                <h1 class="text-xl font-bold text-gray-900">{{ $listing->title }}</h1>
                <p class="text-2xl font-bold text-amber-600 mt-2">{{ Number::currency($listing->price, in: 'BRL') }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $listing->condition->getLabel() }} · {{ $listing->city }}/{{ $listing->state }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $listing->views_count }} visualizações</p>

                <hr class="my-4">

                <p class="text-sm font-medium text-gray-800">Vendedor</p>
                <p class="text-sm text-gray-600">{{ $listing->user->name }}</p>
                @if ($listing->user->phone)
                    <p class="text-sm text-gray-600">{{ $listing->user->phone }}</p>
                @endif

                @auth
                    @if (auth()->id() !== $listing->user_id)
                        <button wire:click="sendMessage"
                            class="mt-4 w-full px-4 py-2 bg-amber-600 text-white rounded-md font-semibold hover:bg-amber-700">
                            Enviar mensagem
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" wire:navigate
                        class="mt-4 block text-center w-full px-4 py-2 bg-amber-600 text-white rounded-md font-semibold hover:bg-amber-700">
                        Entrar para conversar
                    </a>
                @endauth
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <h2 class="text-lg font-semibold text-gray-900 mt-10 mb-3">Você também pode gostar</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ($related as $item)
                <x-listing-card :listing="$item" />
            @endforeach
        </div>
    @endif
</div>
