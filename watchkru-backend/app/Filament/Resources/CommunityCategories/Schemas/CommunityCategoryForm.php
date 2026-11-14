<?php

namespace App\Filament\Resources\CommunityCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CommunityCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('دسته‌بندی')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        TextInput::make('name')
                            ->label('نام دسته')
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
                        Textarea::make('description')
                            ->label('توضیح')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
