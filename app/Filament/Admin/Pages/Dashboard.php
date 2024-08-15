<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
class Dashboard extends Page
{

    public static ?string $title = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static string $view = 'filament.pages.dashboard';

}