<?php

namespace App\Filament\Resources\Communities\Schemas;

use App\Models\Community;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CommunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات انجمن')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('نام انجمن')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label('نشانی (slug)')
                                ->required()
                                ->unique(ignoreRecord: true),
                            TextInput::make('external_url')
                                ->label('لینک انجمن')
                                ->url()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set) => $set(
                                    'normalized_url',
                                    Community::normalizeUrl((string) $state)
                                )),
                            TextInput::make('normalized_url')
                                ->label('لینک نرمال‌شده (تشخیص تکراری)')
                                ->required()
                                ->helperText('به‌طور خودکار از لینک بالا ساخته می‌شود.'),
                        ]),
                        Textarea::make('description')
                            ->label('توضیحات')
                            ->rows(4)
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            Select::make('platform_id')
                                ->label('پلتفرم')
                                ->relationship('platform', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Select::make('community_category_id')
                                ->label('دسته‌بندی')
                                ->relationship('communityCategory', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            TextInput::make('language')
                                ->label('زبان')
                                ->default('fa')
                                ->required(),
                        ]),
                        Select::make('media')
                            ->label('عنوان‌های مرتبط')
                            ->relationship('media', 'title')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),

                Section::make('وضعیت و اعتبار')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('member_count')
                                ->label('تعداد اعضا')
                                ->numeric(),
                            DateTimePicker::make('member_count_verified_at')
                                ->label('زمان تأیید تعداد اعضا'),
                            Select::make('verification_status')
                                ->label('وضعیت بررسی')
                                ->options([
                                    Community::VERIFICATION_VERIFIED => 'تأییدشده',
                                    Community::VERIFICATION_PENDING => 'در انتظار بررسی',
                                    Community::VERIFICATION_NEEDS_REVIEW => 'نیازمند بازبینی',
                                    Community::VERIFICATION_REJECTED => 'ردشده',
                                ])
                                ->required()
                                ->default(Community::VERIFICATION_PENDING),
                            Select::make('activity_status')
                                ->label('وضعیت فعالیت')
                                ->options([
                                    Community::ACTIVITY_ACTIVE => 'فعال',
                                    Community::ACTIVITY_INACTIVE => 'غیرفعال',
                                    Community::ACTIVITY_UNKNOWN => 'نامشخص',
                                ])
                                ->required()
                                ->default(Community::ACTIVITY_UNKNOWN),
                            DateTimePicker::make('last_checked_at')
                                ->label('آخرین بررسی'),
                            TextInput::make('submission_source')
                                ->label('منبع ثبت')
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('created_by')
                                ->label('ثبت‌کننده')
                                ->relationship('creator', 'name')
                                ->searchable(),
                            Select::make('approved_by')
                                ->label('تأییدکننده')
                                ->relationship('approver', 'name')
                                ->searchable(),
                        ]),
                    ]),
            ]);
    }
}
