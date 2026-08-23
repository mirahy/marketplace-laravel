<div>
    <div class="bg-white rounded-lg p-8 mb-8 text-center border border-gray-100">
        <h1 class="text-3xl font-bold text-gray-900">Doações que viram ajuda</h1>
        <p class="text-gray-500 mt-2">Itens usados doados pela comunidade, à venda para apoiar a obra da ADBN.</p>
        <a href="{{ route('listings.index') }}" wire:navigate
            class="inline-block mt-4 px-6 py-2 bg-orange-600 text-white rounded-md font-semibold hover:bg-orange-700">
            Ver todos os anúncios
        </a>
    </div>

    <h2 class="text-lg font-semibold text-gray-900 mb-3">Categorias</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-10">
        @foreach ($categories as $category)
            <a href="{{ route('categories.show', $category) }}" wire:navigate
                class="bg-white border border-gray-100 rounded-lg p-4 text-center text-sm font-medium text-gray-700 hover:border-orange-400 hover:text-orange-600">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    @if ($featured->isNotEmpty())
        <h2 class="text-lg font-semibold text-gray-900 mb-3">Destaques</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-10">
            @foreach ($featured as $listing)
                <x-listing-card :listing="$listing" />
            @endforeach
        </div>
    @endif

    <h2 class="text-lg font-semibold text-gray-900 mb-3">Anúncios recentes</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        @foreach ($recent as $listing)
            <x-listing-card :listing="$listing" />
        @endforeach
    </div>
</div>
