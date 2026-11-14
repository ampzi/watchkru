<?php

namespace App\Filament\Resources\CommunitySubmissions\Schemas;

use App\Models\CommunitySubmission;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommunitySubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ارسال کاربر')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->schema([
                        Select::make('user_id')
                            ->label('کاربر')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('community_id')
                            ->label('انجمن ثبت‌شده')
                            ->relationship('community', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('media_id')
                            ->label('عنوان مرتبط')
                            ->relationship('media', 'title')
                            ->searchable()
                            ->preload(),
                        KeyValue::make('submission_data')
                            ->label('داده‌های خام ارسال‌شده')
                            ->keyLabel('کلید')
                            ->valueLabel('مقدار')
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                CommunitySubmission::STATUS_PENDING => 'در انتظار',
                                CommunitySubmission::STATUS_APPROVED => 'تأییدشده',
                                CommunitySubmission::STATUS_REJECTED => 'ردشده',
                            ])
                            ->default(CommunitySubmission::STATUS_PENDING)
                            ->required(),
                        Textarea::make('admin_note')
                            ->label('یادداشت مدیر')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('reviewed_by')
                            ->label('بررسی‌کننده')
                            ->relationship('reviewer', 'name')
                            ->searchable(),
                        DateTimePicker::make('reviewed_at')
                            ->label('زمان بررسی'),
                    ]),
            ]);
    }
}
