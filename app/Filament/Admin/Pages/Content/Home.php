<?php

namespace App\Filament\Admin\Pages\Content;

use Filament\Pages\Page;

class Home extends Page
{
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Editable Content';
    protected static string $view = 'filament.admin.pages.content.home';
}
