@props(['id' => 'adbn-flame'])

<svg viewBox="0 0 100 130" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $id }}" x1="0%" y1="100%" x2="30%" y2="0%">
            <stop offset="0%" stop-color="#dc2626" />
            <stop offset="55%" stop-color="#ea580c" />
            <stop offset="100%" stop-color="#fbbf24" />
        </linearGradient>
    </defs>
    <path fill="url(#{{ $id }})"
        d="M50 4C41 20 24 30 24 54c0 12 7 20 14 24-4-7-5-15-1-23 3 11 11 16 15 26 5-10 3-19-1-27 7 6 12 15 10 27 10-6 16-17 16-29C77 34 58 24 50 4Z" />
    <path fill="url(#{{ $id }})" opacity="0.85"
        d="M62 40c6 12 10 22 10 34 0 14-9 25-22 25s-22-11-22-25c0-8 3-15 7-21-1 8 2 15 8 19-3-8-2-17 3-24 1 7 6 12 9 19 3-9 5-18 7-27Z" />
</svg>
