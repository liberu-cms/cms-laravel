                ]),
            Select::make('language')
                ->options(Language::pluck('name', 'code'))
                ->required(),
            Select::make('parent_id')
                ->label('Translate from')
                ->options(Content::where('parent_id', null)->pluck('title', 'id'))
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('author.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->sortable(),
                TextColumn::make('status')
                    ->sortable(),
                TextColumn::make('language')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->options(Language::pluck('name', 'code')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('translate')
                    ->icon('heroicon-o-translate')
                    ->url(fn (Content $record) => route('filament.resources.contents.create', ['parent_id' => $record->id]))
                    ->visible(fn (Content $record) => $record->parent_id === null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('author'));
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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}