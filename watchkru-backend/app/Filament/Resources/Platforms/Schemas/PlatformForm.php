<?php

namespace App\Filament\Resources\Platforms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PlatformForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('پلتفرم')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        TextInput::make('name')
                            ->label('نام پلتفرم')
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
                        TextInput::make('icon')
                            ->label('نشانی آیکون')
                            ->url(),
                    ]),
            ]);
    }
}
