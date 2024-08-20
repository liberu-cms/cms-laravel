<?php

namespace App\Livewire;

use App\Models\GuestLayoutManagment;
use App\Models\Menu;
use Illuminate\Http\Request;
use Livewire\Component;

class Webrender extends Component
{
    protected $contents;
    public function mount(Request $request){
        $this->contents =  $this->getModules($request->path());
    }

    protected function getModules($url){
        $menu = Menu::where('url', $url)->first();
        $contents = GuestLayoutManagment::where('fk_menu_id', $menu->id)->get()->toArray();
        if ($contents) {
            $elements = [];
            foreach ($contents as $content) {
                $elements[$content['sort_order']] = $content;
            }
        }
        return $elements ?? [];
    }

    public function render()
    {
        return view('livewire.webrender', [
            'contents' => $this->contents
        ]);
    }
}
