<?php

namespace App\Filament\Resources\CommunityReviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommunityReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('community.name')
                    ->label('انجمن')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('کاربر')
                    ->searchable()
                    ->description(fn ($record) => '@'.$record->user?->username),
                TextColumn::make('rating')
                    ->label('امتیاز')
                    ->badge()
                    ->color(fn (int $state) => match (true) {
                        $state >= 5 => 'success',
                        $state >= 4 => 'info',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('content')
                    ->label('نظر')
                    ->limit(70)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->label('امتیاز')
                    ->options([
                        5 => '۵ ستاره',
                        4 => '۴ ستاره',
                        3 => '۳ ستاره',
                        2 => '۲ ستاره',
                        1 => '۱ ستاره',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
