<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('lang')) {
            $language = $request->get('lang');
            App::setLocale($language);
            session()->put('language', $language);
        } elseif (session()->has('language')) {
            App::setLocale(session()->get('language'));
        }

        return $next($request);
    }
}