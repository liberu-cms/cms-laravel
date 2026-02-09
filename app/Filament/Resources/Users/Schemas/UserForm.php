<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use BezhanSalleh\FilamentShield\Support\Utils;
use Dflydev\DotAccessData\Util;
use Filament\Facades\Filament;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        if (Utils::isTenancyEnabled()) {
            $team = Filament::getTenant();
            setPermissionsTeamId($team->id);
        }

        return $schema->components([
                Section::make('')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->saveRelationshipsUsing(function (User $record, $state) {
                                if (Utils::isTenancyEnabled()) {
                                    $record->roles()->syncWithPivotValues(
                                        $state,
                                        [config('permission.column_names.team_foreign_key') => getPermissionsTeamId()]
                                    );
                                } else {
                                    $record->roles()->sync($state);
                                }
                            })
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        FileUpload::make('profile_photo_path')
                            ->label("Profile Photo")
                            ->image()
                            ->directory('profile-photos'),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->required(),
                ])
            ]);
    }
}
