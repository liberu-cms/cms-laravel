<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    //
    public function show($slug = null)
    {
        $page = Page::where('slug', $slug ?: 'home')->firstOrFail();

        $template = $page->template ?: 'default';

        return view("templates.$template", compact('page'));
    }


}
