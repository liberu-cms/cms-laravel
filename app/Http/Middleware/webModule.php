<?php

namespace App\Http\Middleware;

use App\Models\GuestLayoutManagment;
use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class webModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->getModules($request->path());
        return $next($request);
    }

    protected function getModules($url){
        $menu = Menu::where('url', $url)->first();
        $contents = GuestLayoutManagment::where('fk_menu_id', $menu->id)->get()->toArray();
        if ($contents) {
            $elements = [];
            foreach ($contents as $content) {
                $elements[$content['sort_order']] = $content;
            }
            session(['contents' => $elements]);
        }
        return $contents;
    }
}
