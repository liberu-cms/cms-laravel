                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content_title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('author.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('content_type')
                    ->sortable(),
                TextColumn::make('content_status')
                    ->sortable(),
                TextColumn::make('published_date')
                    ->sortable(),
                TextColumn::make('language.name')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->relationship('language', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('translate')
                    ->icon('heroicon-o-translate')
                    ->action(function (Content $record, array $data): void {
                        // Logic to create a new translation
                    })
                    ->form([
                        Select::make('language_code')
                            ->label('Translate to')
                            ->options(Language::pluck('name', 'code'))
                            ->required(),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with(['author', 'language']));
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('language_code')
                    ->relationship('language', 'name')
                    ->required(),
                TextInput::make('content_title')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('content_body')
                    ->required(),
                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->required(),
                DatePicker::make('published_date'),
                Select::make('content_type')
                    ->options([
                        'article' => 'Article',
                        'page' => 'Page',
                        // Add more content types as needed
                    ])
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('content_status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->required(),
                FileUpload::make('featured_image_url')
                    ->image()
                    ->directory('content-images'),
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