<?php

namespace App\Filament\App\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\CompanyResource\Pages\ListCompanies;
use App\Filament\App\Resources\CompanyResource\Pages\CreateCompany;
use App\Filament\App\Resources\CompanyResource\Pages\EditCompany;
use Filament\Forms;
use Filament\Tables;
use App\Models\Company;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('privacy')
                ->label('Privacy')
                ->required()
                ->max(255),
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->max(255),
            TextInput::make('email')
                ->label('Email')
                ->required()
                ->email()
                ->max(255),
            Toggle::make('is_tenant')
                ->label('Is Tenant')
                ->required(),
            Select::make('status')
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
