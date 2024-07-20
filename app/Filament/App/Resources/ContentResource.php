public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
            Forms\Components\RichEditor::make('body')
                ->required(),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(Content::class, 'slug', fn ($record) => $record)
                ->maxLength(255),
            Forms\Components\Select::make('author_id')
                ->relationship('author', 'name')
                ->required(),
            Forms\Components\DateTimePicker::make('published_at'),
            Forms\Components\Select::make('type')
                ->options([
                    'post' => 'Post',
                    'page' => 'Page',
                ])
                ->required(),
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name'),
            Forms\Components\Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                ])
                ->required(),
            Forms\Components\Section::make('Media')
                ->schema([
                    Forms\Components\FileUpload::make('featured_image_url')
                        ->image()
                        ->maxSize(5120) // 5MB limit
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                        ->disk('public')
                        ->directory('content-images')
                        ->visibility('public')
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9')
                        ->imageResizeTargetWidth('1200')
                        ->imageResizeTargetHeight('675')
                        ->loadingIndicatorPosition('left')
                        ->removeUploadedFileButtonPosition('right')
                        ->uploadProgressIndicatorPosition('left'),
                    Forms\Components\View::make('components.image-preview')
                        ->visible(fn ($record) => $record && $record->featured_image_url)
                        ->viewData(['imageUrl' => fn ($record) => $record ? $record->featured_image_url : null])
                ]),
        ]);
}