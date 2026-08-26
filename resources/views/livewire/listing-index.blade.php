<div x-data="{ showFilters: false }">
    <x-breadcrumbs :items="[
        ['label' => 'Início', 'url' => route('home')],
        ['label' => 'Anúncios', 'url' => route('listings.index')],
        ...($category ? [['label' => $category->name, 'url' => route('categories.show', $category)]] : []),
    ]" />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <button type="button" @click="showFilters = ! showFilters"
            class="md:hidden flex items-center justify-between bg-white border border-gray-100 rounded-lg px-4 py-3 text-sm font-semibold text-gray-800">
            <span>Filtros</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': showFilters }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <aside :class="{ 'hidden': ! showFilters }" class="md:block bg-white border border-gray-100 rounded-lg p-4 h-fit space-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase">Busca</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="O que você procura?"
                    class="mt-1 w-full rounded-md border-gray-300 text-sm">
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase">Categoria</label>
                <x-searchable-select wire:model.live="categoryId" :options="$categoryOptions" :selected="$categoryId" placeholder="Todas" />
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase">Condição</label>
                <select wire:model.live="condition" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="">Todas</option>
                    @foreach ($conditions as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Preço mín.</label>
                    <input type="number" wire:model.live.debounce.300ms="minPrice" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Preço máx.</label>
                    <input type="number" wire:model.live.debounce.300ms="maxPrice" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase">UF</label>
                <x-searchable-select wire:model.live="state" :options="$stateOptions" :selected="$state" placeholder="Todas" />
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase">Ordenar por</label>
                <select wire:model.live="sort" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    <option value="recent">Mais recentes</option>
                    <option value="price_asc">Menor preço</option>
                    <option value="price_desc">Maior preço</option>
                </select>
            </div>
        </aside>

        <div class="md:col-span-3">
            <p class="text-sm text-gray-500 mb-4">{{ $listings->total() }} anúncio(s) encontrado(s)</p>

            @if ($listings->isEmpty())
                <div class="bg-white border border-gray-100 rounded-lg p-10 text-center text-gray-500">
                    Nenhum anúncio encontrado com esses filtros.
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($listings as $listing)
                        <x-listing-card :listing="$listing" />
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $listings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
