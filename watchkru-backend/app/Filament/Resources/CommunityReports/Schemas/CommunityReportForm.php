<?php

namespace App\Filament\Resources\CommunityReports\Schemas;

use App\Models\CommunityReport;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommunityReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('گزارش')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        Select::make('community_id')
                            ->label('انجمن')
                            ->relationship('community', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label('گزارش‌دهنده')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('report_type')
                            ->label('نوع مشکل')
                            ->options([
                                'broken_link' => 'لینک خراب',
                                'inactive' => 'انجمن غیرفعال',
                                'wrong_description' => 'توضیحات نادرست',
                                'wrong_media' => 'عنوان مرتبط اشتباه',
                                'wrong_platform' => 'پلتفرم اشتباه',
                                'spam' => 'اسپم',
                                'inappropriate' => 'محتوای نامناسب',
                                'duplicate' => 'تکراری',
                                'other' => 'سایر',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                CommunityReport::STATUS_OPEN => 'باز',
                                CommunityReport::STATUS_RESOLVED => 'رسیدگی‌شده',
                                CommunityReport::STATUS_DISMISSED => 'ردشده',
                            ])
                            ->default(CommunityReport::STATUS_OPEN)
                            ->required(),
                        Textarea::make('description')
                            ->label('توضیح کاربر')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('resolved_by')
                            ->label('رسیدگی‌کننده')
                            ->relationship('resolver', 'name')
                            ->searchable(),
                        DateTimePicker::make('resolved_at')
                            ->label('زمان رسیدگی'),
                    ]),
            ]);
    }
}
