<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('release_year', 'desc')
            ->columns([
                ImageColumn::make('poster_path')
                    ->label('پوستر')
                    ->height(64)
                    ->width(44)
                    ->defaultImageUrl(null),
                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Media $record) => $record->original_title)
                    ->weight('medium'),
                TextColumn::make('media_type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === Media::TYPE_MOVIE ? 'فیلم' : 'سریال')
                    ->color(fn (string $state) => $state === Media::TYPE_MOVIE ? 'warning' : 'info'),
                TextColumn::make('release_year')
                    ->label('سال')
                    ->sortable(),
                TextColumn::make('number_of_seasons')
                    ->label('فصل')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('number_of_episodes')
                    ->label('قسمت')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('genres.name')
                    ->label('ژانرها')
                    ->badge()
                    ->separator(','),
                TextColumn::make('communities_count')
                    ->label('انجمن')
                    ->counts('communities')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('آخرین ویرایش')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('media_type')
                    ->label('نوع')
                    ->options([
                        Media::TYPE_MOVIE => 'فیلم',
                        Media::TYPE_SERIES => 'سریال',
                    ]),
                SelectFilter::make('genres')
                    ->label('ژانر')
                    ->relationship('genres', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
