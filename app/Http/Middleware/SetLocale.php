<?php

namespace App\Http\Middleware;

use App\Enums\SupportedLocale;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = SupportedLocale::tryFrom(session('locale', ''))?->value
            ?? config('app.locale');

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
