<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات اصلی')
                    ->icon('heroicon-o-film')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('عنوان')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('original_title')
                                ->label('عنوان اصلی'),
                            TextInput::make('slug')
                                ->label('نشانی (slug)')
                                ->required()
                                ->unique(ignoreRecord: true),
                            Select::make('media_type')
                                ->label('نوع')
                                ->options([
                                    Media::TYPE_MOVIE => 'فیلم',
                                    Media::TYPE_SERIES => 'سریال',
                                ])
                                ->required()
                                ->live(),
                            Select::make('status')
                                ->label('وضعیت پخش')
                                ->options([
                                    'released' => 'منتشرشده',
                                    'upcoming' => 'در آینده',
                                    'ongoing' => 'در حال پخش',
                                    'ended' => 'پایان‌یافته',
                                ]),
                            TextInput::make('language')
                                ->label('زبان اصلی')
                                ->default('en')
                                ->required(),
                        ]),
                        TagsInput::make('alternative_titles')
                            ->label('عنوان‌های جایگزین / فارسی')
                            ->placeholder('عنوان را اضافه کنید')
                            ->columnSpanFull(),
                        RichEditor::make('overview')
                            ->label('خلاصه داستان')
                            ->columnSpanFull(),
                    ]),

                Section::make('دسته‌بندی و ژانر')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Select::make('genres')
                            ->label('ژانرها')
                            ->relationship('genres', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')->label('نام ژانر')->required(),
                                TextInput::make('slug')->label('slug')->required()->unique('genres', 'slug'),
                            ]),
                    ]),

                Section::make('جزئیات پخش')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(3)->schema([
                            DatePicker::make('release_date')
                                ->label('تاریخ انتشار')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $set('release_year', date('Y', strtotime((string) $state)));
                                    }
                                }),
                            TextInput::make('release_year')
                                ->label('سال انتشار')
                                ->numeric(),
                            TextInput::make('runtime')
                                ->label('مدت (دقیقه)')
                                ->numeric(),
                            TextInput::make('number_of_seasons')
                                ->label('تعداد فصل')
                                ->numeric()
                                ->visible(fn (callable $get) => $get('media_type') === Media::TYPE_SERIES),
                            TextInput::make('number_of_episodes')
                                ->label('تعداد قسمت')
                                ->numeric()
                                ->visible(fn (callable $get) => $get('media_type') === Media::TYPE_SERIES),
                        ]),
                    ]),

                Section::make('عوامل و رسانه')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        TextInput::make('director')
                            ->label('کارگردان / سازنده'),
                        TagsInput::make('cast')
                            ->label('بازیگران')
                            ->placeholder('نام بازیگر را اضافه کنید')
                            ->columnSpanFull(),
                    ]),

                Section::make('پوستر و تصویر پس‌زمینه')
                    ->icon('heroicon-o-photo')
                    ->description('لینک تصویر را وارد کنید؛ آدرس کامل (https://…) یا مسیر داخلی مثل /images/posters/the-100.jpg هر دو پذیرفته می‌شود.')
                    ->schema([
                        TextInput::make('poster_path')
                            ->label('لینک پوستر')
                            ->placeholder('/images/posters/the-100.jpg')
                            ->helperText('نسبت پیشنهادی ۲:۳ عمودی (مثلاً ۶۰۰×۹۰۰ پیکسل). این تصویر در کارت‌ها و صفحه عنوان نمایش داده می‌شود.')
                            ->rules(['nullable', 'string', 'max:2048'])
                            ->rule('regex:#^(https?://|/).+#'),
                        TextInput::make('backdrop_path')
                            ->label('لینک تصویر پس‌زمینه')
                            ->placeholder('https://example.com/backdrop.jpg')
                            ->helperText('تصویر افقی برای بالای صفحه عنوان؛ اگر خالی بماند از پوستر با حالت محو استفاده می‌شود.')
                            ->rules(['nullable', 'string', 'max:2048'])
                            ->rule('regex:#^(https?://|/).+#'),
                    ]),

                Section::make('داده‌های خارجی')
                    ->columnSpanFull()
                    ->icon('heroicon-o-cloud')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('external_provider')
                                ->label('منبع داده (مثلاً tmdb)'),
                            TextInput::make('external_id')
                                ->label('شناسه در منبع'),
                        ]),
                    ]),
            ]);
    }
}
