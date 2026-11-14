<?php

namespace App\Filament\Widgets;

use App\Models\CommunityReview;
use Filament\Widgets\ChartWidget;

class ReviewRatingChart extends ChartWidget
{
    protected ?string $heading = 'توزیع امتیاز نظرات کاربران';

    protected ?string $description = 'تعداد نظرات برای هر امتیاز از ۱ تا ۵.';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = CommunityReview::query()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        return [
            'datasets' => [
                [
                    'label' => 'نظر',
                    'data' => collect(range(1, 5))->map(fn (int $rating) => (int) ($counts[$rating] ?? 0))->all(),
                    'backgroundColor' => ['#dc2626', '#ea580c', '#d97706', '#65a30d', '#059669'],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => ['۱', '۲', '۳', '۴', '۵'],
        ];
    }
}
