
                        'archived' => 'Archived',
                    ]),
                ImageColumn::make('featured_image_url')
                    ->label('Featured Image URL'),
                Forms\Components\TagsInput::make('tag')
                    ->label('Tags')
                    ->separator(',')
                    ->relationship('tag', 'tag_name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content_title')
                    ->label('Content Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author.author_name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                BelongsToColumn::make('category.content_category_name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('content_status')
                    ->label('Content Status')
                    ->colors([
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                    ]),
                BooleanColumn::make('is_featured')
                    ->label('Is Featured')
                    ->trueValue(true)
                    ->falseValue(false)
                    ->sortable(),
                ImageColumn::make('featured_image_url')
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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
