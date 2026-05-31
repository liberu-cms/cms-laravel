<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function show(Collection $collection): View
    {
        $items = $collection->items()
            ->latest()
            ->paginate(10);

        return view('collection', ['collection' => $collection, 'items' => $items]);
    }
}
