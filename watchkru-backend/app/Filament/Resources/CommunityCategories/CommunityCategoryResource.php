<?php

namespace App\Filament\Resources\CommunityCategories;

use App\Filament\Resources\CommunityCategories\Pages\CreateCommunityCategory;
use App\Filament\Resources\CommunityCategories\Pages\EditCommunityCategory;
use App\Filament\Resources\CommunityCategories\Pages\ListCommunityCategories;
use App\Filament\Resources\CommunityCategories\Schemas\CommunityCategoryForm;
use App\Filament\Resources\CommunityCategories\Tables\CommunityCategoriesTable;
use App\Models\CommunityCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommunityCategoryResource extends Resource
{
    protected static ?string $model = CommunityCategory::class;

    protected static ?string $modelLabel = 'دسته‌بندی انجمن';

    protected static ?string $pluralModelLabel = 'دسته‌بندی انجمن‌ها';

    protected static \UnitEnum|string|null $navigationGroup = 'مدیریت محتوا';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    public static function form(Schema $schema): Schema
    {
        return CommunityCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunityCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommunityCategories::route('/'),
            'create' => CreateCommunityCategory::route('/create'),
            'edit' => EditCommunityCategory::route('/{record}/edit'),
        ];
    }
}
