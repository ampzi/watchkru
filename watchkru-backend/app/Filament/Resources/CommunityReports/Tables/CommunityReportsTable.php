<?php

namespace App\Filament\Resources\CommunityReports\Tables;

use App\Models\CommunityReport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommunityReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('community.name')
                    ->label('انجمن')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('گزارش‌دهنده')
                    ->placeholder('مهمان')
                    ->toggleable(),
                TextColumn::make('report_type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'broken_link' => 'لینک خراب',
                        'inactive' => 'غیرفعال',
                        'wrong_description' => 'توضیح نادرست',
                        'wrong_media' => 'عنوان اشتباه',
                        'wrong_platform' => 'پلتفرم اشتباه',
                        'spam' => 'اسپم',
                        'inappropriate' => 'نامناسب',
                        'duplicate' => 'تکراری',
                        default => 'سایر',
                    }),
                TextColumn::make('description')
                    ->label('توضیح')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        CommunityReport::STATUS_RESOLVED => 'رسیدگی‌شده',
                        CommunityReport::STATUS_DISMISSED => 'ردشده',
                        default => 'باز',
                    })
                    ->color(fn (string $state) => match ($state) {
                        CommunityReport::STATUS_RESOLVED => 'success',
                        CommunityReport::STATUS_DISMISSED => 'gray',
                        default => 'danger',
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
                        CommunityReport::STATUS_OPEN => 'باز',
                        CommunityReport::STATUS_RESOLVED => 'رسیدگی‌شده',
                        CommunityReport::STATUS_DISMISSED => 'ردشده',
                    ]),
                SelectFilter::make('report_type')
                    ->label('نوع مشکل')
                    ->options([
                        'broken_link' => 'لینک خراب',
                        'inactive' => 'غیرفعال',
                        'wrong_description' => 'توضیح نادرست',
                        'wrong_media' => 'عنوان اشتباه',
                        'wrong_platform' => 'پلتفرم اشتباه',
                        'spam' => 'اسپم',
                        'inappropriate' => 'نامناسب',
                        'duplicate' => 'تکراری',
                        'other' => 'سایر',
                    ]),
            ])
            ->recordActions([
                Action::make('resolve')
                    ->label('رسیدگی شد')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (CommunityReport $record) => $record->status === CommunityReport::STATUS_OPEN)
                    ->action(fn (CommunityReport $record) => $record->update([
                        'status' => CommunityReport::STATUS_RESOLVED,
                        'resolved_by' => auth()->id(),
                        'resolved_at' => now(),
                    ])),
                Action::make('dismiss')
                    ->label('رد گزارش')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (CommunityReport $record) => $record->status === CommunityReport::STATUS_OPEN)
                    ->action(fn (CommunityReport $record) => $record->update([
                        'status' => CommunityReport::STATUS_DISMISSED,
                        'resolved_by' => auth()->id(),
                        'resolved_at' => now(),
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
