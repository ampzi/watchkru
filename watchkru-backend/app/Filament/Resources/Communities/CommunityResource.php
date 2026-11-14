<?php

namespace App\Filament\Resources\Communities;

use App\Filament\Resources\Communities\Pages\CreateCommunity;
use App\Filament\Resources\Communities\Pages\EditCommunity;
use App\Filament\Resources\Communities\Pages\ListCommunities;
use App\Filament\Resources\Communities\Schemas\CommunityForm;
use App\Filament\Resources\Communities\Tables\CommunitiesTable;
use App\Models\Community;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;

    protected static ?string $modelLabel = 'انجمن';

    protected static ?string $pluralModelLabel = 'انجمن‌ها';

    protected static ?string $navigationLabel = 'انجمن‌ها';

    protected static \UnitEnum|string|null $navigationGroup = 'مدیریت انجمن‌ها';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return CommunityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunitiesTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'external_url'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'پلتفرم' => $record->platform?->name,
            'وضعیت' => $record->verification_status,
        ];
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
            'index' => ListCommunities::route('/'),
            'create' => CreateCommunity::route('/create'),
            'edit' => EditCommunity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
