<?php

namespace App\Filament\Widgets;

use App\Models\Media;
use Filament\Widgets\ChartWidget;

class MediaTypesChart extends ChartWidget
{
    protected ?string $heading = 'ترکیب آرشیو: فیلم و سریال';

    protected ?string $description = 'تعداد عنوان‌ها به تفکیک نوع اثر.';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Media::query()
            ->selectRaw('media_type, count(*) as total')
            ->groupBy('media_type')
            ->pluck('total', 'media_type');

        return [
            'datasets' => [
                [
                    'label' => 'عنوان',
                    'data' => [
                        (int) ($counts[Media::TYPE_MOVIE] ?? 0),
                        (int) ($counts[Media::TYPE_SERIES] ?? 0),
                    ],
                    'backgroundColor' => ['#d97706', '#0d9488'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['فیلم', 'سریال'],
        ];
    }
}
