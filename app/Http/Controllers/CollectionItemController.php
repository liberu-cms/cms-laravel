<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\View\View;

class CollectionItemController extends Controller
{
    public function show(Collection $collection, CollectionItem $item): View
    {
        $item = $collection->items()
            ->where('slug', $item->slug)
            ->firstOrFail();

        return view('item', ['collection' => $collection, 'item' => $item]);
    }
}
