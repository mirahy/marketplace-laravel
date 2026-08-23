@use('Illuminate\Support\Facades\Storage')
@use('Illuminate\Support\Str')

<div class="max-w-2xl mx-auto bg-white border border-gray-100 rounded-lg p-6">
    <h1 class="text-xl font-bold text-gray-900 mb-6">
        {{ $listing ? 'Editar anúncio' : 'Novo anúncio' }}
    </h1>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
    @endif

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Título</label>
            <input type="text" wire:model="title" class="mt-1 w-full rounded-md border-gray-300">
            @error('title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">Categoria</label>
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
            <label class="text-sm font-medium text-gray-700">Descrição</label>
            <textarea wire:model="description" rows="4" class="mt-1 w-full rounded-md border-gray-300"></textarea>
            @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-700">Preço (R$)</label>
                <input type="number" step="0.01" wire:model="price" class="mt-1 w-full rounded-md border-gray-300">
                @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Condição</label>
                <select wire:model="condition" class="mt-1 w-full rounded-md border-gray-300">
                    @foreach ($conditions as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
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
            @error('newPhotos.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            @if (count($newPhotos) > 0)
                <div class="mt-2 grid grid-cols-4 gap-2">
                    @foreach ($newPhotos as $photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="w-full h-20 object-cover rounded-md">
                    @endforeach
                </div>
            @endif
        </div>

        <button type="submit"
            class="w-full px-4 py-2 bg-orange-600 text-white rounded-md font-semibold hover:bg-orange-700">
            {{ $listing ? 'Salvar alterações' : 'Publicar anúncio' }}
        </button>
    </form>
</div>
