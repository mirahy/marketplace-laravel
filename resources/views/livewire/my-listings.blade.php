@use('Illuminate\Support\Number')

<div>
    <x-breadcrumbs :items="[
        ['label' => __('Início'), 'url' => route('home')],
        ['label' => __('Meus anúncios')],
    ]" />

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900">{{ __('Meus anúncios') }}</h1>
        @can('create', \App\Models\Listing::class)
            <a href="{{ route('listings.create') }}" wire:navigate
                class="px-4 py-2 bg-orange-600 text-white rounded-md font-semibold text-sm hover:bg-orange-700">
                {{ __('Novo anúncio') }}
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
    @endif

    @if ($listings->isEmpty())
        <div class="bg-white border border-gray-100 rounded-lg p-10 text-center text-gray-500">
            {{ __('Você ainda não tem anúncios.') }}
        </div>
    @else
        <div class="space-y-3">
            @foreach ($listings as $listing)
                <div class="bg-white border border-gray-100 rounded-lg p-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-medium text-gray-900">{{ $listing->title }}</p>
                        <p class="text-sm text-gray-500">
                            {{ Number::currency($listing->price, in: 'BRL') }} ·
                            <span class="font-medium">{{ $listing->status->getLabel() }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-sm shrink-0">
                        <a href="{{ route('listings.show', $listing) }}" wire:navigate class="text-gray-600 hover:underline">{{ __('Ver') }}</a>
                        <a href="{{ route('listings.edit', $listing) }}" wire:navigate class="text-gray-600 hover:underline">{{ __('Editar') }}</a>

                        @if ($listing->status->value === 'ativo')
                            <button wire:click="pause({{ $listing->id }})" class="text-gray-600 hover:underline">{{ __('Pausar') }}</button>
                            <button wire:click="markAsSold({{ $listing->id }})" class="text-gray-600 hover:underline">{{ __('Marcar vendido') }}</button>
                        @elseif ($listing->status->value === 'pausado')
                            <button wire:click="reactivate({{ $listing->id }})" class="text-gray-600 hover:underline">{{ __('Reativar') }}</button>
                        @endif

                        <button wire:click="delete({{ $listing->id }})" wire:confirm="{{ __('Tem certeza que deseja excluir este anúncio?') }}"
                            class="text-red-600 hover:underline">{{ __('Excluir') }}</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
