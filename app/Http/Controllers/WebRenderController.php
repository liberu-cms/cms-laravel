<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuestLayoutManagment;
use App\Models\Menu;

class WebRenderController extends Controller
{
    public $contents;

    public function __construct(Request $request)
    {
        $this->contents = $this->getModules($request->path());
    }

    public function getModules($url){
        $menu = Menu::where('url', $url)->first();
        $contents = GuestLayoutManagment::where('fk_menu_id', $menu->id)->get()->toArray();
        if ($contents) {
            $elements = [];
            foreach ($contents as $content) {
                if($content['is_active'] != 0) {
                    $elements[$content['sort_order']] = $content;
                }
            }
        }
        return $elements ?? [];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('livewire.webrender', [
            'contents' => $this->contents
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
