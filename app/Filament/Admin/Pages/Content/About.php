<?php

namespace App\Filament\Admin\Pages\Content;

use Filament\Pages\Page;

class About extends Page
{
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationGroup = 'Editable Content';
    protected static string $view = 'filament.admin.pages.content.about';
}
