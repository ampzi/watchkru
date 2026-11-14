<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === ContactMessage::STATUS_NEW ? 'جدید' : 'خوانده‌شده')
                    ->color(fn (string $state) => $state === ContactMessage::STATUS_NEW ? 'warning' : 'gray'),
                TextColumn::make('subject')
                    ->label('موضوع')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('name')
                    ->label('فرستنده')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('body')
                    ->label('متن')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        ContactMessage::STATUS_NEW => 'جدید',
                        ContactMessage::STATUS_READ => 'خوانده‌شده',
                    ]),
            ])
            ->recordActions([
                Action::make('markRead')
                    ->label('خوانده شد')
                    ->icon('heroicon-o-check')
                    ->visible(fn (ContactMessage $record) => $record->status === ContactMessage::STATUS_NEW)
                    ->action(fn (ContactMessage $record) => $record->update([
                        'status' => ContactMessage::STATUS_READ,
                        'read_at' => now(),
                    ])),
                DeleteAction::make(),
            ]);
    }
}
