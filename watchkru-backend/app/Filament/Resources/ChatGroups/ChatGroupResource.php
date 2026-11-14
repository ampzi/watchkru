<?php

namespace App\Filament\Resources\ChatGroups;

use App\Filament\Resources\ChatGroups\Pages\ListChatGroups;
use App\Models\ChatGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatGroupResource extends Resource
{
    protected static ?string $model = ChatGroup::class;

    protected static ?string $modelLabel = 'گروه گفت‌وگو';

    protected static ?string $pluralModelLabel = 'گروه‌های گفت‌وگو';

    protected static ?string $navigationLabel = 'گروه‌های گفت‌وگو';

    protected static \UnitEnum|string|null $navigationGroup = 'گفت‌وگو';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\ChatGroups\Tables\ChatGroupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatGroups::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()
            ->whereIn('status', [ChatGroup::STATUS_PENDING, ChatGroup::STATUS_DELETION_REQUESTED])
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
