<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Liberu\Cms\Pages\Models\Page;

class PageController extends Controller
{
    public function show(?string $slug = null): View
    {
        $page = Page::where('slug', $slug ?? 'home')->firstOrFail();

        $template = $page->template ?: 'default';

        return view("templates.$template", ['page' => $page]);
    }
}
