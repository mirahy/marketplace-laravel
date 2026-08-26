@use('Illuminate\Support\Facades\Storage')
@use('Illuminate\Support\Str')
@use('Illuminate\Support\Number')

<div x-init="
    const key = 'adbn_viewed_listings';
    let history = [];
    try { history = JSON.parse(localStorage.getItem(key) || '[]'); } catch (e) { history = []; }
    $wire.setViewHistory(history);
    try {
        const updated = [{{ $listing->id }}, ...history.filter(id => id !== {{ $listing->id }})].slice(0, 20);
        localStorage.setItem(key, JSON.stringify(updated));
    } catch (e) {}
">
    <x-breadcrumbs :items="[
        ['label' => 'Início', 'url' => route('home')],
        ['label' => 'Anúncios', 'url' => route('listings.index')],
        ...($listing->category ? [['label' => $listing->category->name, 'url' => route('categories.show', $listing->category)]] : []),
        ['label' => $listing->title],
    ]" />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-2">
            @php $imageCount = $listing->images->count(); @endphp
            <div x-data="{ active: 0, total: {{ $imageCount }} }" class="relative w-full h-64 sm:h-96 rounded-lg bg-gray-100 overflow-hidden">
                @forelse ($listing->images as $i => $image)
                    <img x-show="active === {{ $i }}" x-cloak
                        src="{{ Str::startsWith($image->path, 'http') ? $image->path : Storage::url($image->path) }}"
                        class="absolute inset-0 w-full h-full object-cover" alt="{{ $listing->title }}">
                @empty
                    <div class="absolute inset-0"></div>
                @endforelse

                @if ($imageCount > 1)
                    <button type="button" @click="active = (active - 1 + total) % total"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white shadow flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button type="button" @click="active = (active + 1) % total"
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white shadow flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                @endif
            </div>

            <div class="mt-6 bg-white border border-gray-100 rounded-lg p-6">
                <h2 class="font-semibold text-gray-900 mb-2">Descrição</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $listing->description }}</p>
            </div>
        </div>

        <div>
            <div class="bg-white border border-gray-100 rounded-lg p-6 sticky top-4">
                <h1 class="text-xl font-bold text-gray-900">{{ $listing->title }}</h1>
                <p class="text-2xl font-bold text-orange-600 mt-2">{{ Number::currency($listing->price, in: 'BRL') }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $listing->condition->getLabel() }} · {{ $listing->city }}/{{ $listing->state }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $listing->views_count }} visualizações</p>

                <hr class="my-4">

                <p class="text-sm font-medium text-gray-800">Vendedor</p>
                @if ($listing->user)
                    <p class="text-sm text-gray-600">{{ $listing->user->name }}</p>
                    @auth
                        @if ($listing->user->phone && $listing->user->show_phone)
                            <p class="text-sm text-gray-600 flex items-center gap-1.5">
                                <a href="https://wa.me/{{ $listing->user->whatsappNumber() }}" target="_blank" rel="noopener noreferrer" title="Conversar no WhatsApp">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="h-4 w-4 text-green-500 shrink-0">
                                        <path fill="currentColor" d="M16.004 2.667c-7.363 0-13.333 5.97-13.333 13.333 0 2.351.616 4.646 1.787 6.665l-1.898 6.933a1 1 0 0 0 1.226 1.226l6.933-1.898a13.28 13.28 0 0 0 6.665 1.787h.006c7.363 0 13.333-5.97 13.333-13.333s-5.97-13.333-13.333-13.333zm0 24.24h-.005a10.87 10.87 0 0 1-5.87-1.72l-.42-.25-4.114 1.126 1.126-4.114-.25-.42a10.87 10.87 0 0 1-1.72-5.87c0-6.008 4.892-10.9 10.9-10.9 2.911 0 5.649 1.135 7.71 3.196a10.83 10.83 0 0 1 3.19 7.71c0 6.008-4.892 10.9-10.9 10.9zm5.984-8.157c-.328-.164-1.94-.957-2.24-1.066-.301-.109-.52-.164-.738.164-.219.328-.847 1.066-1.038 1.284-.191.219-.383.246-.71.082-.328-.164-1.385-.51-2.638-1.627-.975-.869-1.633-1.942-1.824-2.27-.191-.328-.02-.505.144-.668.148-.148.328-.383.492-.574.164-.191.219-.328.328-.547.109-.219.055-.41-.027-.574-.082-.164-.738-1.78-1.012-2.436-.267-.64-.538-.554-.738-.564l-.629-.011a1.21 1.21 0 0 0-.875.41c-.301.328-1.148 1.121-1.148 2.735s1.175 3.172 1.339 3.39c.164.219 2.312 3.53 5.603 4.949.783.338 1.394.54 1.87.691.786.25 1.5.215 2.065.13.63-.094 1.94-.793 2.213-1.559.273-.766.273-1.422.191-1.559-.082-.137-.301-.219-.629-.383z" />
                                    </svg>
                                </a>
                                {{ $listing->user->phone }}
                            </p>
                        @endif
                    @endauth
                @else
                    <p class="text-sm text-gray-400">Vendedor indisponível</p>
                @endif

                @auth
                    @if ($listing->user && auth()->id() !== $listing->user_id)
                        <button wire:click="sendMessage"
                            class="mt-4 w-full px-4 py-2 bg-orange-600 text-white rounded-md font-semibold hover:bg-orange-700">
                            Enviar mensagem
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" wire:navigate
                        class="mt-4 block text-center w-full px-4 py-2 bg-orange-600 text-white rounded-md font-semibold hover:bg-orange-700">
                        Entrar para conversar
                    </a>
                @endauth
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <h2 class="text-lg font-semibold text-gray-900 mt-10 mb-3">Você também pode gostar</h2>
        <div class="relative" x-data>
            <div x-ref="relatedTrack" class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory scrollbar-hide">
                @foreach ($related as $item)
                    <div class="w-40 sm:w-52 shrink-0 snap-start">
                        <x-listing-card :listing="$item" />
                    </div>
                @endforeach
            </div>

            @if ($related->count() > 2)
                <button type="button" @click="$refs.relatedTrack.scrollBy({ left: -300, behavior: 'smooth' })"
                    class="absolute -left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white border border-gray-100 shadow-md flex items-center justify-center hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <button type="button" @click="$refs.relatedTrack.scrollBy({ left: 300, behavior: 'smooth' })"
                    class="absolute -right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white border border-gray-100 shadow-md flex items-center justify-center hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            @endif
        </div>
    @endif
</div>
