<?php

namespace App\Filament\Resources\ChatGroups\Tables;

use App\Models\ChatGroup;
use App\Notifications\ChatAlert;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChatGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('نام گروه')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (ChatGroup $record) => $record->description),
                TextColumn::make('creator.name')
                    ->label('سازنده')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        ChatGroup::STATUS_PENDING => 'در انتظار تأیید',
                        ChatGroup::STATUS_APPROVED => 'تأییدشده',
                        ChatGroup::STATUS_REJECTED => 'ردشده',
                        ChatGroup::STATUS_DELETION_REQUESTED => 'درخواست حذف',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        ChatGroup::STATUS_APPROVED => 'success',
                        ChatGroup::STATUS_PENDING => 'warning',
                        ChatGroup::STATUS_REJECTED => 'danger',
                        ChatGroup::STATUS_DELETION_REQUESTED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('members_count')
                    ->label('اعضا')
                    ->counts('members')
                    ->numeric(),
                TextColumn::make('messages_count')
                    ->label('پیام‌ها')
                    ->counts('messages')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('ساخته‌شده')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->label('بررسی')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        ChatGroup::STATUS_PENDING => 'در انتظار تأیید',
                        ChatGroup::STATUS_APPROVED => 'تأییدشده',
                        ChatGroup::STATUS_REJECTED => 'ردشده',
                        ChatGroup::STATUS_DELETION_REQUESTED => 'درخواست حذف',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('تأیید')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ChatGroup $record) => in_array($record->status, [
                        ChatGroup::STATUS_PENDING,
                        ChatGroup::STATUS_REJECTED,
                    ], true))
                    ->requiresConfirmation()
                    ->action(function (ChatGroup $record) {
                        $record->update([
                            'status' => ChatGroup::STATUS_APPROVED,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $record->creator?->notify(new ChatAlert(
                            'گروه شما تأیید شد',
                            "«{$record->name}» اکنون در صفحه گفت‌وگو در دسترس است.",
                            '/chat?group='.$record->id,
                            'success',
                            'heroicon-o-check-badge',
                        ));
                    }),
                Action::make('reject')
                    ->label('رد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ChatGroup $record) => $record->status !== ChatGroup::STATUS_REJECTED)
                    ->form([
                        Textarea::make('admin_note')
                            ->label('دلیل رد (برای سازنده ارسال می‌شود)')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (ChatGroup $record, array $data) {
                        $record->update([
                            'status' => ChatGroup::STATUS_REJECTED,
                            'admin_note' => $data['admin_note'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $record->creator?->notify(new ChatAlert(
                            'گروه شما رد شد',
                            "دلیل: {$data['admin_note']}",
                            '/chat',
                            'danger',
                            'heroicon-o-x-circle',
                        ));
                    }),
                Action::make('approveDeletion')
                    ->label('تأیید حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (ChatGroup $record) => $record->status === ChatGroup::STATUS_DELETION_REQUESTED)
                    ->requiresConfirmation()
                    ->modalDescription('گروه و پیام‌های آن حذف می‌شوند (قابل بازیابی از سطل زباله نیست).')
                    ->action(function (ChatGroup $record) {
                        $creator = $record->creator;
                        $record->forceDelete();

                        $creator?->notify(new ChatAlert(
                            'گروه شما حذف شد',
                            'درخواست حذف گروه شما تأیید شد.',
                            '/chat',
                            'danger',
                            'heroicon-o-trash',
                        ));
                    }),
            ]);
    }
}
