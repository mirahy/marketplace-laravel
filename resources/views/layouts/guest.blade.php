@use('App\Enums\SupportedLocale')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="icon" type="image/png" href="{{ asset('img/adb.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#0b1440]">
            <div class="w-full sm:max-w-md flex justify-end px-6" x-data="{ open: false }">
                <div class="relative">
                    <button @click="open = ! open" class="text-sm text-slate-300 hover:text-white flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c-2.485 0-4.5-4.03-4.5-9s2.015-9 4.5-9 4.5 4.03 4.5 9-2.015 9-4.5 9Zm-9-9h18" />
                        </svg>
                        {{ SupportedLocale::from(app()->getLocale())->getLabel() }}
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
            </div>

            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="w-16 h-20" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
