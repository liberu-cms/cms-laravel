<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\Http\Request;

class CollectionItemController extends Controller
{
    public function show(Collection $collection, CollectionItem $item)
    {
        $item = $collection->items()
            ->where('slug', $item->slug)
            ->firstOrFail();

        return view('item', compact('collection', 'item'));
    }
}
