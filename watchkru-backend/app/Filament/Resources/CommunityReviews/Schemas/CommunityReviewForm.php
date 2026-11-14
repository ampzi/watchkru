<?php

namespace App\Filament\Resources\CommunityReviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommunityReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('نظر کاربر')
                    ->icon('heroicon-o-star')
                    ->schema([
                        Select::make('community_id')
                            ->label('انجمن')
                            ->relationship('community', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label('کاربر')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('rating')
                            ->label('امتیاز')
                            ->options([
                                5 => '۵ — عالی',
                                4 => '۴ — خوب',
                                3 => '۳ — متوسط',
                                2 => '۲ — ضعیف',
                                1 => '۱ — بد',
                            ])
                            ->required(),
                        Textarea::make('content')
                            ->label('متن نظر')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
