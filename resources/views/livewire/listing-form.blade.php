@use('Illuminate\Support\Facades\Storage')
@use('Illuminate\Support\Str')

<div class="max-w-2xl mx-auto">
    <x-breadcrumbs :items="[
        ['label' => 'Início', 'url' => route('home')],
        ['label' => 'Meus anúncios', 'url' => route('listings.mine')],
        ['label' => $listing ? 'Editar anúncio' : 'Novo anúncio'],
    ]" />

    <div class="bg-white border border-gray-100 rounded-lg p-6">
    <h1 class="text-xl font-bold text-gray-900 mb-6">
        {{ $listing ? 'Editar anúncio' : 'Novo anúncio' }}
    </h1>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
    @endif

    <p class="text-xs text-gray-500 mb-4"><span class="text-red-500">*</span> Campos obrigatórios. Os demais campos são opcionais.</p>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Título <span class="text-red-500">*</span></label>
            <input type="text" wire:model="title" class="mt-1 w-full rounded-md border-gray-300">
            @error('title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">Categoria <span class="text-red-500">*</span></label>
            <x-searchable-select wire:model="categoryId" wire:key="category-select-{{ $categoryOptions->count() }}" :options="$categoryOptions" :selected="$categoryId" placeholder="Selecione" />
            @error('categoryId') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            @if (! $showNewCategoryForm)
                <button type="button" wire:click="$set('showNewCategoryForm', true)"
                    class="mt-2 text-sm text-orange-600 hover:text-orange-700 font-medium">
                    + Cadastrar nova categoria
                </button>
            @else
                <div class="mt-2 flex gap-2">
                    <input type="text" wire:model="newCategoryName" placeholder="Nome da nova categoria"
                        class="flex-1 rounded-md border-gray-300 text-sm">
                    <button type="button" wire:click="createCategory"
                        class="px-3 py-1.5 bg-orange-600 text-white rounded-md text-sm font-semibold hover:bg-orange-700">
                        Salvar categoria
                    </button>
                    <button type="button" wire:click="$set('showNewCategoryForm', false)"
                        class="px-3 py-1.5 text-gray-500 text-sm hover:text-gray-700">
                        Cancelar
                    </button>
                </div>
                @error('newCategoryName') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mt-1">A categoria fica pendente de aprovação de um admin, mas já pode ser usada neste anúncio.</p>
            @endif
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">Descrição <span class="text-red-500">*</span></label>
            <textarea wire:model="description" rows="4" class="mt-1 w-full rounded-md border-gray-300"></textarea>
            @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-700">Preço (R$) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" wire:model="price" class="mt-1 w-full rounded-md border-gray-300">
                @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Condição <span class="text-red-500">*</span></label>
                <select wire:model="condition" class="mt-1 w-full rounded-md border-gray-300">
                    @foreach ($conditions as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $hasAddressData = $addressType || $addressStreet || $addressNumber || $addressNeighborhood || $addressComplement || $state || $city;
        @endphp

        <div class="border-t border-gray-100 pt-4" x-data="{ open: {{ $hasAddressData ? 'true' : 'false' }} }">
            <button type="button" @click="open = ! open"
                class="flex items-center justify-between w-full text-left">
                <span class="text-sm font-medium text-gray-800">Endereço <span class="text-gray-400 font-normal">(opcional)</span></span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div x-show="open" x-cloak class="grid grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="text-sm font-medium text-gray-700">Tipo de logradouro</label>
                    <select wire:model="addressType" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Selecione</option>
                        @foreach ($addressTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->getLabel() }}</option>
                        @endforeach
                    </select>
                    @error('addressType') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Logradouro</label>
                    <input type="text" wire:model="addressStreet" placeholder="Nome da rua"
                        class="mt-1 w-full rounded-md border-gray-300">
                    @error('addressStreet') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Número</label>
                    <input type="text" wire:model="addressNumber" class="mt-1 w-full rounded-md border-gray-300">
                    @error('addressNumber') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Bairro</label>
                    <input type="text" wire:model="addressNeighborhood" class="mt-1 w-full rounded-md border-gray-300">
                    @error('addressNeighborhood') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="text-sm font-medium text-gray-700">Complemento</label>
                    <input type="text" wire:model="addressComplement" placeholder="Apto, bloco, ponto de referência..."
                        class="mt-1 w-full rounded-md border-gray-300">
                    @error('addressComplement') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">UF</label>
                    <x-searchable-select wire:model.live="state" :options="$stateOptions" :selected="$state" placeholder="Selecione" />
                    @error('state') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Cidade</label>
                    <x-searchable-select wire:model="city" wire:key="city-select-{{ $state }}" :options="$cityOptions" :selected="$city" :placeholder="$state ? 'Selecione' : 'Selecione a UF primeiro'" />
                    @error('city') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        @if (count($existingImages) > 0)
            <div>
                <label class="text-sm font-medium text-gray-700">Fotos atuais</label>
                <div class="mt-2 grid grid-cols-4 gap-2">
                    @foreach ($existingImages as $image)
                        <div class="relative">
                            <img src="{{ Str::startsWith($image->path, 'http') ? $image->path : Storage::url($image->path) }}"
                                class="w-full h-20 object-cover rounded-md">
                            <button type="button" wire:click="removeExistingImage({{ $image->id }})"
                                class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 text-xs">×</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <label class="text-sm font-medium text-gray-700">Adicionar fotos</label>
            <input type="file" wire:model="newPhotos" multiple accept="image/*" class="mt-1 w-full text-sm">

            <div wire:loading.flex wire:target="newPhotos" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                <svg class="animate-spin h-4 w-4 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Carregando imagens, aguarde...
            </div>

            @error('newPhotos.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            @if (count($newPhotos) > 0)
                <div class="mt-2 grid grid-cols-4 gap-2">
                    @foreach ($newPhotos as $photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="w-full h-20 object-cover rounded-md">
                    @endforeach
                </div>
            @endif
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="newPhotos"
            class="w-full px-4 py-2 bg-orange-600 text-white rounded-md font-semibold hover:bg-orange-700 disabled:opacity-60 disabled:cursor-not-allowed">
            {{ $listing ? 'Salvar alterações' : 'Publicar anúncio' }}
        </button>
    </form>
    </div>
</div>
