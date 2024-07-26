<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\LanguageMiddleware;

Route::middleware([LanguageMiddleware::class])->group(function () {
    Route::get('/', \App\Http\Livewire\Homepage::class);

    Route::get('/{lang}', function ($lang) {
        App::setLocale($lang);
        return redirect('/');
    })->where('lang', '[a-z]{2}');

    // Add other localized routes here
});
