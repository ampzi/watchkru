<?php

namespace App\Filament\Widgets;

use App\Models\Community;
use Filament\Widgets\ChartWidget;

class TopCommunitiesChart extends ChartWidget
{
    protected ?string $heading = 'پرجمعیت‌ترین انجمن‌ها';

    protected ?string $description = 'پنج انجمن تأییدشده با بیشترین تعداد عضو.';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $communities = Community::query()
            ->verified()
            ->whereNotNull('member_count')
            ->orderByDesc('member_count')
            ->take(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'عضو',
                    'data' => $communities->pluck('member_count')->map(fn ($count) => (int) $count)->all(),
                    'backgroundColor' => '#d97706',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $communities->pluck('name')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
