<?php

namespace App\Filament\Widgets;

use App\Models\Community;
use App\Models\CommunityReview;
use App\Models\Media;
use Filament\Widgets\ChartWidget;

class ContentGrowthChart extends ChartWidget
{
    protected ?string $heading = 'روند رشد محتوا';

    protected ?string $description = 'تعداد ثبت‌های جدید در شش ماه گذشته.';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $offset) => now()->subMonths($offset));

        $series = fn (string $model) => $months->map(fn ($month) => $model::query()
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count())->all();

        return [
            'datasets' => [
                [
                    'label' => 'انجمن‌ها',
                    'data' => $series(Community::class),
                    'borderColor' => '#0d9488',
                    'backgroundColor' => 'rgba(13, 148, 136, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'عنوان‌ها',
                    'data' => $series(Media::class),
                    'borderColor' => '#d97706',
                    'backgroundColor' => 'rgba(217, 119, 6, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'نظرات',
                    'data' => $series(CommunityReview::class),
                    'borderColor' => '#0284c7',
                    'backgroundColor' => 'rgba(2, 132, 199, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->translatedFormat('M Y'))->all(),
        ];
    }
}
