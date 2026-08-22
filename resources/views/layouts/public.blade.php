<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('home') }}" wire:navigate class="text-xl font-bold text-amber-600">
                            {{ config('app.name') }}
                        </a>
                        <div class="hidden sm:flex gap-6 text-sm font-medium text-gray-600">
                            <a href="{{ route('listings.index') }}" wire:navigate class="hover:text-amber-600">Anúncios</a>
                            @auth
                                <a href="{{ route('listings.mine') }}" wire:navigate class="hover:text-amber-600">Meus anúncios</a>
                                <a href="{{ route('messages.index') }}" wire:navigate class="hover:text-amber-600">Mensagens</a>
                            @endauth
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('listings.create') }}" wire:navigate
                                class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                                Anunciar
                            </a>
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = ! open" class="text-sm text-gray-600 hover:text-gray-900">
                                    {{ auth()->user()->name }}
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak
                                    class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-10">
                                    <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sair</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900">Entrar</a>
                            <a href="{{ route('register') }}" wire:navigate
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Cadastrar
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </body>
</html>
