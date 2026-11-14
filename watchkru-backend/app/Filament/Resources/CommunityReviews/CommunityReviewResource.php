<?php

namespace App\Filament\Resources\CommunityReviews;

use App\Filament\Resources\CommunityReviews\Pages\CreateCommunityReview;
use App\Filament\Resources\CommunityReviews\Pages\EditCommunityReview;
use App\Filament\Resources\CommunityReviews\Pages\ListCommunityReviews;
use App\Filament\Resources\CommunityReviews\Schemas\CommunityReviewForm;
use App\Filament\Resources\CommunityReviews\Tables\CommunityReviewsTable;
use App\Models\CommunityReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommunityReviewResource extends Resource
{
    protected static ?string $model = CommunityReview::class;

    protected static ?string $modelLabel = 'نظر';

    protected static ?string $pluralModelLabel = 'نظرات کاربران';

    protected static \UnitEnum|string|null $navigationGroup = 'مدیریت انجمن‌ها';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    public static function form(Schema $schema): Schema
    {
        return CommunityReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunityReviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommunityReviews::route('/'),
            'create' => CreateCommunityReview::route('/create'),
            'edit' => EditCommunityReview::route('/{record}/edit'),
        ];
    }
}
