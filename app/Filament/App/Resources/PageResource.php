public static function getPages(): array
{
    return [
        'index' => Pages\ListPages::route('/'),
        'create' => Pages\CreatePage::route('/create'),
        'edit' => Pages\EditPage::route('/{record}/edit'),
    ];
}