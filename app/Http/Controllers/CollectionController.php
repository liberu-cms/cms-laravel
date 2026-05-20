<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function show(Collection $collection)
    {
        $items = $collection->items()
            ->latest()
            ->paginate(10);

        return view('collection', compact('collection', 'items'));
    }
}
