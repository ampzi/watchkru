<?php

namespace App\Filament\Resources\CommunityReports;

use App\Filament\Resources\CommunityReports\Pages\CreateCommunityReport;
use App\Filament\Resources\CommunityReports\Pages\EditCommunityReport;
use App\Filament\Resources\CommunityReports\Pages\ListCommunityReports;
use App\Filament\Resources\CommunityReports\Schemas\CommunityReportForm;
use App\Filament\Resources\CommunityReports\Tables\CommunityReportsTable;
use App\Models\CommunityReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommunityReportResource extends Resource
{
    protected static ?string $model = CommunityReport::class;

    protected static ?string $modelLabel = 'گزارش';

    protected static ?string $pluralModelLabel = 'گزارش‌های کاربران';

    protected static \UnitEnum|string|null $navigationGroup = 'بررسی و گزارش‌ها';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    public static function form(Schema $schema): Schema
    {
        return CommunityReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunityReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommunityReports::route('/'),
            'create' => CreateCommunityReport::route('/create'),
            'edit' => EditCommunityReport::route('/{record}/edit'),
        ];
    }

    /** Hide the create page: reports always come from users. */
    public static function canCreate(): bool
    {
        return false;
    }
}
