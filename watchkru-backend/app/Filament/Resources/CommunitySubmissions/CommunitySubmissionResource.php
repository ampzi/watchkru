<?php

namespace App\Filament\Resources\CommunitySubmissions;

use App\Filament\Resources\CommunitySubmissions\Pages\CreateCommunitySubmission;
use App\Filament\Resources\CommunitySubmissions\Pages\EditCommunitySubmission;
use App\Filament\Resources\CommunitySubmissions\Pages\ListCommunitySubmissions;
use App\Filament\Resources\CommunitySubmissions\Schemas\CommunitySubmissionForm;
use App\Filament\Resources\CommunitySubmissions\Tables\CommunitySubmissionsTable;
use App\Models\CommunitySubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommunitySubmissionResource extends Resource
{
    protected static ?string $model = CommunitySubmission::class;

    protected static ?string $modelLabel = 'ارسال کاربر';

    protected static ?string $pluralModelLabel = 'ارسال‌های کاربران';

    protected static \UnitEnum|string|null $navigationGroup = 'بررسی و گزارش‌ها';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    public static function form(Schema $schema): Schema
    {
        return CommunitySubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunitySubmissionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommunitySubmissions::route('/'),
            'create' => CreateCommunitySubmission::route('/create'),
            'edit' => EditCommunitySubmission::route('/{record}/edit'),
        ];
    }

    /** Submissions are created by the public API, never by hand. */
    public static function canCreate(): bool
    {
        return false;
    }
}
