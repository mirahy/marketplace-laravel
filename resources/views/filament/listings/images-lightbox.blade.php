@use('Illuminate\Support\Facades\Storage')
@use('Illuminate\Support\Str')

@php
    $images = $images ?? collect();
    $total = $images->count();
    $initial = $initial ?? 0;
@endphp

<style>
    .adbn-lightbox-nav {
        background-color: rgba(255, 255, 255, 0.15);
        border: none;
        cursor: pointer;
    }
    .adbn-lightbox-nav:hover {
        background-color: rgba(255, 255, 255, 0.3);
    }
</style>

<div
    x-data="{ active: {{ $initial }}, total: {{ $total }} }"
    x-on:keydown.arrow-left.window="active = (active - 1 + total) % total"
    x-on:keydown.arrow-right.window="active = (active + 1) % total"
    style="position: relative;"
>
    <div style="position: relative; width: 100%; height: 26rem; background-color: #111827; border-radius: 0.5rem; overflow: hidden; display: flex; align-items: center; justify-content: center;">
        @forelse ($images as $i => $image)
            <img
                x-show="active === {{ $i }}" x-cloak
                src="{{ Str::startsWith($image->path, 'http') ? $image->path : Storage::url($image->path) }}"
                style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain;"
                alt=""
            >
        @empty
            <p style="color: #ffffff;">{{ __('Nenhuma imagem.') }}</p>
        @endforelse

        @if ($total > 1)
            <button type="button" @click="active = (active - 1 + total) % total"
                class="adbn-lightbox-nav"
                style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 2.5rem; height: 2.5rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center;"
                title="{{ __('Anterior') }}">
                <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.375rem; width: 1.375rem; color: #ffffff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </button>
            <button type="button" @click="active = (active + 1) % total"
                class="adbn-lightbox-nav"
                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); width: 2.5rem; height: 2.5rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center;"
                title="{{ __('Próxima') }}">
                <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.375rem; width: 1.375rem; color: #ffffff;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        @endif
    </div>

    @if ($total > 1)
        <p style="text-align: center; font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;" x-text="(active + 1) + ' / ' + total"></p>
    @endif
</div>
