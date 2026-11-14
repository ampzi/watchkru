<?php

namespace App\Filament\Resources\Communities\Tables;

use App\Models\Community;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Community $record) => $record->external_url)
                    ->weight('medium'),
                TextColumn::make('platform.name')
                    ->label('پلتفرم')
                    ->badge()
                    ->sortable(),
                TextColumn::make('communityCategory.name')
                    ->label('دسته')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('member_count')
                    ->label('اعضا')
                    ->numeric()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('verification_status')
                    ->label('بررسی')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        Community::VERIFICATION_VERIFIED => 'تأییدشده',
                        Community::VERIFICATION_PENDING => 'در انتظار',
                        Community::VERIFICATION_NEEDS_REVIEW => 'بازبینی',
                        Community::VERIFICATION_REJECTED => 'ردشده',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        Community::VERIFICATION_VERIFIED => 'success',
                        Community::VERIFICATION_PENDING => 'warning',
                        Community::VERIFICATION_NEEDS_REVIEW => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('activity_status')
                    ->label('فعالیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        Community::ACTIVITY_ACTIVE => 'فعال',
                        Community::ACTIVITY_INACTIVE => 'غیرفعال',
                        default => 'نامشخص',
                    })
                    ->color(fn (string $state) => match ($state) {
                        Community::ACTIVITY_ACTIVE => 'success',
                        Community::ACTIVITY_INACTIVE => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reviews_count')
                    ->label('نظر')
                    ->counts('reviews')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_checked_at')
                    ->label('آخرین بررسی')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('verification_status')
                    ->label('وضعیت بررسی')
                    ->options([
                        Community::VERIFICATION_VERIFIED => 'تأییدشده',
                        Community::VERIFICATION_PENDING => 'در انتظار',
                        Community::VERIFICATION_NEEDS_REVIEW => 'نیازمند بازبینی',
                        Community::VERIFICATION_REJECTED => 'ردشده',
                    ]),
                SelectFilter::make('activity_status')
                    ->label('وضعیت فعالیت')
                    ->options([
                        Community::ACTIVITY_ACTIVE => 'فعال',
                        Community::ACTIVITY_INACTIVE => 'غیرفعال',
                        Community::ACTIVITY_UNKNOWN => 'نامشخص',
                    ]),
                SelectFilter::make('platform')
                    ->label('پلتفرم')
                    ->relationship('platform', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('verify')
                    ->label('تأیید')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Community $record) => $record->verification_status !== Community::VERIFICATION_VERIFIED)
                    ->action(fn (Community $record) => $record->update([
                        'verification_status' => Community::VERIFICATION_VERIFIED,
                        'approved_by' => auth()->id(),
                        'last_checked_at' => now(),
                    ])),
                Action::make('needsReview')
                    ->label('بازبینی')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (Community $record) => $record->verification_status !== Community::VERIFICATION_NEEDS_REVIEW)
                    ->action(fn (Community $record) => $record->update([
                        'verification_status' => Community::VERIFICATION_NEEDS_REVIEW,
                    ])),
                Action::make('reject')
                    ->label('رد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Community $record) => $record->verification_status !== Community::VERIFICATION_REJECTED)
                    ->action(fn (Community $record) => $record->update([
                        'verification_status' => Community::VERIFICATION_REJECTED,
                    ])),
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
