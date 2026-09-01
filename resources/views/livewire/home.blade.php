<div>
    <div class="bg-white rounded-lg p-8 mb-8 text-center border border-gray-100">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Doações que viram ajuda') }}</h1>
        <p class="text-gray-500 mt-2">{{ __('Itens usados doados pela comunidade, à venda para apoiar a obra da ADBN.') }}</p>
        <a href="{{ route('listings.index') }}" wire:navigate
            class="inline-block mt-4 px-6 py-2 bg-orange-600 text-white rounded-md font-semibold hover:bg-orange-700">
            {{ __('Ver todos os anúncios') }}
        </a>
    </div>

    <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Categorias') }}</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-10">
        @foreach ($categories as $category)
            <a href="{{ route('categories.show', $category) }}" wire:navigate
                class="bg-white border border-gray-100 rounded-lg p-4 text-center text-sm font-medium text-gray-700 hover:border-orange-400 hover:text-orange-600">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    @if ($featured->isNotEmpty())
        <div class="mb-10" x-data>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Destaques') }}</h2>
                <div class="flex gap-2">
                    <button type="button" @click="$refs.featuredCarousel.scrollBy({ left: -280, behavior: 'smooth' })"
                        class="w-8 h-8 rounded-full bg-white border border-gray-200 hover:bg-gray-50 flex items-center justify-center" aria-label="{{ __('Anterior') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button type="button" @click="$refs.featuredCarousel.scrollBy({ left: 280, behavior: 'smooth' })"
                        class="w-8 h-8 rounded-full bg-white border border-gray-200 hover:bg-gray-50 flex items-center justify-center" aria-label="{{ __('Próxima') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
            </div>
            <div x-ref="featuredCarousel" style="scrollbar-width: none;"
                class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-2 [&::-webkit-scrollbar]:hidden">
                @foreach ($featured as $listing)
                    <div class="w-40 sm:w-48 md:w-56 shrink-0 snap-start">
                        <x-listing-card :listing="$listing" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Anúncios recentes') }}</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @foreach ($recent as $listing)
            <x-listing-card :listing="$listing" />
        @endforeach
    </div>
</div>
