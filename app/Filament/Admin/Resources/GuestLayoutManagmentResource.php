<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GuestLayoutManagmentResource\Pages;
use App\Filament\Admin\Resources\GuestLayoutManagmentResource\RelationManagers;
use App\Models\GuestLayoutManagment;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuestLayoutManagmentResource extends Resource
{
    protected static ?string $model = GuestLayoutManagment::class;
    protected static ?string $navigationGroup = 'Guest Configuration';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Layout managment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->autocapitalize('words'),
                TextInput::make('fk_menu_id')->numeric(),
                TextInput::make('sort_order')->numeric(),
                ToggleButtons::make('is_active')->label('Display the content')->boolean()->inline(),
                Section::make()->columns([
                    'sm' => 12,
                    'xl' => 12,
                    '2xl' => 12,
                ])->schema([
                    MarkdownEditor::make('content')
                        ->toolbarButtons([
                            'attachFiles',
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'heading',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'table',
                            'undo',
                        ])->columnSpan('full')
                ])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuestLayoutManagments::route('/'),
            'create' => Pages\CreateGuestLayoutManagment::route('/create'),
            'edit' => Pages\EditGuestLayoutManagment::route('/{record}/edit'),
        ];
    }
}
