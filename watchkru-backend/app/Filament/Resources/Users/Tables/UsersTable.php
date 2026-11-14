<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
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
                    ->description(fn (User $record) => '@'.$record->username)
                    ->weight('medium'),
                TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('role')
                    ->label('نقش')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        User::ROLE_SUPER_ADMIN => 'مدیر ارشد',
                        User::ROLE_ADMIN => 'مدیر',
                        User::ROLE_MODERATOR => 'ناظر',
                        User::ROLE_GUEST => 'مهمان',
                        default => 'کاربر',
                    })
                    ->color(fn (string $state) => match ($state) {
                        User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN => 'danger',
                        User::ROLE_MODERATOR => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('communities_count')
                    ->label('انجمن‌ها')
                    ->counts('communities')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('reviews_count')
                    ->label('نظرات')
                    ->counts('reviews')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('عضویت')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('نقش')
                    ->options([
                        User::ROLE_SUPER_ADMIN => 'مدیر ارشد',
                        User::ROLE_ADMIN => 'مدیر',
                        User::ROLE_MODERATOR => 'ناظر',
                        User::ROLE_USER => 'کاربر',
                        User::ROLE_GUEST => 'مهمان',
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
