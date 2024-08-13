<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
class Dashboard extends Page
{

    public static ?string $title = 'Dashboard';

    public static $icon = 'heroicon-o-home';

    public function view()
    {
        return view('filament.pages.dashboard');
    }
}