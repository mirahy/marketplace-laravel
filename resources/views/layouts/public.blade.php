@use('App\Enums\SupportedLocale')
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <meta property="og:title" content="{{ $ogTitle ?? $title ?? config('app.name') }}">
        <meta property="og:description" content="{{ $ogDescription ?? __('Itens usados doados pela comunidade, à venda para apoiar a obra da ADBN.') }}">
        <meta property="og:image" content="{{ $ogImage ?? asset('img/adb.png') }}">
        <meta property="og:url" content="{{ $ogUrl ?? url()->current() }}">
        <meta property="og:type" content="{{ $ogType ?? 'website' }}">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta name="twitter:card" content="summary_large_image">

        <link rel="icon" type="image/png" href="{{ asset('img/adb.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <nav class="bg-[#0b1440] border-b border-white/10" x-data="{ mobileMenuOpen: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-[74px] items-center gap-4">
                    <div class="flex items-center gap-8">
                        <button type="button" @click="mobileMenuOpen = ! mobileMenuOpen"
                            class="sm:hidden text-slate-300 hover:text-white">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': mobileMenuOpen, 'inline-flex': ! mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <a href="{{ route('home') }}" wire:navigate class="hidden sm:flex items-center gap-2">
                            <x-adbn-logo class="h-14 w-auto" />
                            <span class="text-lg font-bold text-white leading-tight">
                                <span class="text-orange-400 font-semibold">Marketplace</span>
                            </span>
                        </a>
                        <div class="hidden sm:flex gap-6 text-sm font-medium text-slate-300">
                            <a href="{{ route('listings.index') }}" wire:navigate class="hover:text-orange-400">{{ __('Anúncios') }}</a>
                            @auth
                                @can('create', \App\Models\Listing::class)
                                    <a href="{{ route('listings.mine') }}" wire:navigate class="hover:text-orange-400">{{ __('Meus anúncios') }}</a>
                                @endcan
                                <a href="{{ route('messages.index') }}" wire:navigate class="hover:text-orange-400">{{ __('Mensagens') }}</a>
                            @endauth
                        </div>
                    </div>

                    <form action="{{ route('listings.index') }}" method="GET" class="hidden sm:block flex-1 max-w-md">
                        <div class="relative">
                            <button type="submit" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </button>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Buscar anúncios...') }}"
                                class="w-full rounded-md border-gray-300 text-sm pl-9 pr-3 py-2 bg-white/95">
                        </div>
                    </form>

                    <div class="flex items-center gap-4 ms-auto">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = ! open" class="text-sm text-slate-300 hover:text-white flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9 4.5 4.03 4.5 9-2.015 9-4.5 9Zm-9-9h18" />
                                </svg>
                                <span class="hidden sm:inline">{{ SupportedLocale::from(app()->getLocale())->getLabel() }}</span>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak
                                class="absolute right-0 mt-2 w-32 bg-white rounded-md shadow-lg py-1 z-10">
                                @foreach (SupportedLocale::cases() as $locale)
                                    <a href="{{ route('locale.switch', $locale->value) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() === $locale->value ? 'font-semibold' : '' }}">
                                        {{ $locale->getLabel() }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @auth
                            @can('create', \App\Models\Listing::class)
                                <a href="{{ route('listings.create') }}" wire:navigate
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
                                    {{ __('Anunciar') }}
                                </a>
                            @endcan
                            <livewire:notification-bell />
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = ! open" class="text-sm text-slate-300 hover:text-white">
                                    {{ auth()->user()->name }}
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                    class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-10">
                                    @if (auth()->user()->is_admin)
                                        <a href="/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('Painel Admin') }}</a>
                                    @endif
                                    <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('Perfil') }}</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('Sair') }}</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="text-sm text-slate-300 hover:text-white">{{ __('Entrar') }}</a>
                            <a href="{{ route('register') }}" wire:navigate
                                class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
                                {{ __('Cadastrar') }}
                            </a>
                        @endauth
                    </div>
                </div>

                <div x-show="mobileMenuOpen" x-cloak @click.outside="mobileMenuOpen = false"
                    class="sm:hidden pb-4 space-y-1">
                    <a href="{{ route('home') }}" wire:navigate @click="mobileMenuOpen = false"
                        class="flex items-center gap-2 px-2 py-2">
                        <x-adbn-logo class="h-12 w-auto" />
                        <span class="text-base font-bold text-white leading-tight">
                            <span class="text-orange-400 font-semibold">Marketplace</span>
                        </span>
                    </a>
                    <div class="border-t border-white/10 my-2"></div>

                    <form action="{{ route('listings.index') }}" method="GET" class="px-2 pb-2">
                        <div class="relative">
                            <button type="submit" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </button>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Buscar anúncios...') }}"
                                class="w-full rounded-md border-gray-300 text-sm pl-9 pr-3 py-2 bg-white/95">
                        </div>
                    </form>

                    <a href="{{ route('listings.index') }}" wire:navigate @click="mobileMenuOpen = false"
                        class="block px-2 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-orange-400 hover:bg-white/5">
                        {{ __('Anúncios') }}
                    </a>
                    @auth
                        @can('create', \App\Models\Listing::class)
                            <a href="{{ route('listings.mine') }}" wire:navigate @click="mobileMenuOpen = false"
                                class="block px-2 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-orange-400 hover:bg-white/5">
                                {{ __('Meus anúncios') }}
                            </a>
                        @endcan
                        <a href="{{ route('messages.index') }}" wire:navigate @click="mobileMenuOpen = false"
                            class="block px-2 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-orange-400 hover:bg-white/5">
                            {{ __('Mensagens') }}
                        </a>
                    @endauth

                    <div class="border-t border-white/10 my-2"></div>
                    <div class="flex gap-2 px-2 py-2">
                        @foreach (SupportedLocale::cases() as $locale)
                            <a href="{{ route('locale.switch', $locale->value) }}"
                                class="px-2 py-1 rounded-md text-xs font-medium {{ app()->getLocale() === $locale->value ? 'bg-orange-600 text-white' : 'text-slate-300 hover:bg-white/5' }}">
                                {{ $locale->getLabel() }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </body>
</html>
