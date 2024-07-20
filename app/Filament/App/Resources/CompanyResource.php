<?php

namespace App\Filament\App\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Company;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\CompanyResource\Pages;
use App\Filament\App\Resources\CompanyResource\RelationManagers;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('privacy')
                ->label('Privacy')
                ->required()
                ->max(255),
            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->max(255),
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->required()
                ->email()
                ->max(255),
            Forms\Components\Toggle::make('is_tenant')
                ->label('Is Tenant')
                ->required(),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->required()
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('privacy')
                ->label('Privacy')
                ->searchable()
                ->sortable(),
            TextColumn::make('name')
                ->label('Name')
                ->searchable()
                ->sortable(),
            TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->sortable(),
            TextColumn::make('is_tenant')
                ->label('Is Tenant')
                ->searchable()
                ->sortable(),
            TextColumn::make('status')
                ->label('Status')
                ->searchable()
                ->sortable(),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
