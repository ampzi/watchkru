<?php

namespace App\Filament\Resources\CommunitySubmissions\Tables;

use App\Models\CommunitySubmission;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommunitySubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('کاربر')
                    ->searchable()
                    ->description(fn (CommunitySubmission $record) => '@'.$record->user?->username),
                TextColumn::make('media.title')
                    ->label('عنوان')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('community.name')
                    ->label('انجمن')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('submission_data.external_url')
                    ->label('لینک ارسال‌شده')
                    ->limit(38)
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        CommunitySubmission::STATUS_APPROVED => 'تأییدشده',
                        CommunitySubmission::STATUS_REJECTED => 'ردشده',
                        default => 'در انتظار',
                    })
                    ->color(fn (string $state) => match ($state) {
                        CommunitySubmission::STATUS_APPROVED => 'success',
                        CommunitySubmission::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        CommunitySubmission::STATUS_PENDING => 'در انتظار',
                        CommunitySubmission::STATUS_APPROVED => 'تأییدشده',
                        CommunitySubmission::STATUS_REJECTED => 'ردشده',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('تأیید')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (CommunitySubmission $record) => $record->status !== CommunitySubmission::STATUS_APPROVED)
                    ->action(fn (CommunitySubmission $record) => $record->update([
                        'status' => CommunitySubmission::STATUS_APPROVED,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),
                Action::make('reject')
                    ->label('رد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CommunitySubmission $record) => $record->status !== CommunitySubmission::STATUS_REJECTED)
                    ->schema([
                        Textarea::make('admin_note')
                            ->label('دلیل رد')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(fn (CommunitySubmission $record, array $data) => $record->update([
                        'status' => CommunitySubmission::STATUS_REJECTED,
                        'admin_note' => $data['admin_note'] ?? null,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),
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
