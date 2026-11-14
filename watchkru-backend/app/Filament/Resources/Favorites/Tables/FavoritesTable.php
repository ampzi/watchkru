<?php

namespace App\Filament\Resources\Favorites\Tables;

use App\Models\Favorite;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FavoritesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('کاربر')
                    ->searchable()
                    ->description(fn (Favorite $record) => '@'.$record->user?->username),
                TextColumn::make('favoritable_type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => class_basename($state) === 'Media' ? 'عنوان' : 'انجمن'),
                TextColumn::make('favoritable.title')
                    ->label('عنوان')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('favoritable_id')
                    ->label('شناسه')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
