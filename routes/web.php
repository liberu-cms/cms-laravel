<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CollectionItemController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'show']);
Route::get('/{collection:slug}/{item:slug}', [CollectionItemController::class, 'show']);

Route::get('/{slug}', function ($slug) {
    // 1. Try page
    if ($page = \App\Models\Page::where('slug', $slug)->first()) {
        return app(PageController::class)->show($slug);
    }

    // 2. Try collection
    if ($collection = \App\Models\Collection::where('slug', $slug)->first()) {
        return app(CollectionController::class)->show($collection);
    }

    abort(404);
})->name('pages.show');
