<?php

namespace App\Filament\Admin\Pages\Content;

use Filament\Pages\Page;

class Contact extends Page
{
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Editable Content';
    protected static string $view = 'filament.admin.pages.content.contact';
}
