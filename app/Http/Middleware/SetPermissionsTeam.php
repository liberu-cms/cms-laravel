<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;

class SetPermissionsTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Utils::isTenancyEnabled() && ($team = Filament::getTenant())) {
            setPermissionsTeamId($team->id);
        }        
        return $next($request);
    }
}
