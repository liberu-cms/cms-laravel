<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class webRender extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $contents = session('contents');
        return view('your.view.name', compact('contents'));
    }
}
