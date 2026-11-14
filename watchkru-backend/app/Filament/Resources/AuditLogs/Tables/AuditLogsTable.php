<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('زمان')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('کاربر')
                    ->placeholder('سیستم')
                    ->searchable(),
                TextColumn::make('action')
                    ->label('عملیات')
                    ->badge()
                    ->searchable(),
                TextColumn::make('entity_type')
                    ->label('موجودیت')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->searchable(),
                TextColumn::make('entity_id')
                    ->label('شناسه')
                    ->numeric(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('new_values')
                    ->label('تغییرات')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : (string) $state)
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('عملیات')
                    ->options(fn () => \App\Models\AuditLog::query()
                        ->distinct()
                        ->pluck('action', 'action')
                        ->all()),
            ]);
    }
}
