<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <nav class="bg-[#0b1440] border-b border-white/10" x-data="{ mobileMenuOpen: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                            <x-adbn-logo class="h-9 w-auto" />
                            <span class="text-lg font-bold text-white leading-tight">
                                ADBN <span class="text-orange-400 font-semibold">Marketplace</span>
                            </span>
                        </a>
                        <div class="hidden sm:flex gap-6 text-sm font-medium text-slate-300">
                            <a href="{{ route('listings.index') }}" wire:navigate class="hover:text-orange-400">Anúncios</a>
                            @auth
                                <a href="{{ route('listings.mine') }}" wire:navigate class="hover:text-orange-400">Meus anúncios</a>
                                <a href="{{ route('messages.index') }}" wire:navigate class="hover:text-orange-400">Mensagens</a>
                            @endauth
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="button" @click="mobileMenuOpen = ! mobileMenuOpen"
                            class="sm:hidden text-slate-300 hover:text-white">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': mobileMenuOpen, 'inline-flex': ! mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        @auth
                            <a href="{{ route('listings.create') }}" wire:navigate
                                class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
                                Anunciar
                            </a>
                            <livewire:notification-bell />
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = ! open" class="text-sm text-slate-300 hover:text-white">
                                    {{ auth()->user()->name }}
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                    class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-10">
                                    @if (auth()->user()->is_admin)
                                        <a href="/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Painel Admin</a>
                                    @endif
                                    <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sair</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="text-sm text-slate-300 hover:text-white">Entrar</a>
                            <a href="{{ route('register') }}" wire:navigate
                                class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
                                Cadastrar
                            </a>
                        @endauth
                    </div>
                </div>

                <div x-show="mobileMenuOpen" x-cloak @click.outside="mobileMenuOpen = false"
                    class="sm:hidden pb-4 space-y-1">
                    <a href="{{ route('listings.index') }}" wire:navigate @click="mobileMenuOpen = false"
                        class="block px-2 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-orange-400 hover:bg-white/5">
                        Anúncios
                    </a>
                    @auth
                        <a href="{{ route('listings.mine') }}" wire:navigate @click="mobileMenuOpen = false"
                            class="block px-2 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-orange-400 hover:bg-white/5">
                            Meus anúncios
                        </a>
                        <a href="{{ route('messages.index') }}" wire:navigate @click="mobileMenuOpen = false"
                            class="block px-2 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-orange-400 hover:bg-white/5">
                            Mensagens
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </body>
</html>
