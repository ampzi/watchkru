<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('حساب کاربری')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('نام نمایشی')
                                ->required(),
                            TextInput::make('username')
                                ->label('نام کاربری')
                                ->required()
                                ->unique(ignoreRecord: true),
                            TextInput::make('email')
                                ->label('ایمیل')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true),
                            TextInput::make('password')
                                ->label('رمز عبور')
                                ->password()
                                ->revealable()
                                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                ->dehydrated(fn ($state) => filled($state))
                                ->required(fn (string $operation) => $operation === 'create')
                                ->helperText('در حالت ویرایش، خالی بگذارید تا رمز فعلی تغییر نکند.'),
                            Select::make('role')
                                ->label('نقش')
                                ->options([
                                    User::ROLE_GUEST => 'مهمان',
                                    User::ROLE_USER => 'کاربر',
                                    User::ROLE_MODERATOR => 'ناظر',
                                    User::ROLE_ADMIN => 'مدیر',
                                    User::ROLE_SUPER_ADMIN => 'مدیر ارشد',
                                ])
                                ->default(User::ROLE_USER)
                                ->required(),
                            DateTimePicker::make('email_verified_at')
                                ->label('زمان تأیید ایمیل'),
                        ]),
                        TextInput::make('avatar')
                            ->label('نشانی تصویر پروفایل')
                            ->url(),
                        Textarea::make('bio')
                            ->label('بیوگرافی')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
