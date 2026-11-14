<?php

namespace App\Filament\Resources\Favorites;

use App\Filament\Resources\Favorites\Pages\EditFavorite;
use App\Filament\Resources\Favorites\Pages\ListFavorites;
use App\Filament\Resources\Favorites\Tables\FavoritesTable;
use App\Models\Favorite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FavoriteResource extends Resource
{
    protected static ?string $model = Favorite::class;

    protected static ?string $modelLabel = 'علاقه‌مندی';

    protected static ?string $pluralModelLabel = 'علاقه‌مندی‌ها';

    protected static \UnitEnum|string|null $navigationGroup = 'کاربران و دسترسی';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    public static function table(Table $table): Table
    {
        return FavoritesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFavorites::route('/'),
            'edit' => EditFavorite::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
