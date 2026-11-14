<?php

namespace App\Filament\Widgets;

use App\Models\Community;
use Filament\Widgets\ChartWidget;

class CommunityVerificationChart extends ChartWidget
{
    protected ?string $heading = 'وضعیت بررسی انجمن‌ها';

    protected ?string $description = 'هر انجمن می‌تواند تأییدشده، در انتظار، ردشده یا نیازمند بازبینی باشد.';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $counts = Community::query()
            ->selectRaw('verification_status, count(*) as total')
            ->groupBy('verification_status')
            ->pluck('total', 'verification_status');

        return [
            'datasets' => [
                [
                    'label' => 'انجمن',
                    'data' => [
                        (int) ($counts[Community::VERIFICATION_VERIFIED] ?? 0),
                        (int) ($counts[Community::VERIFICATION_PENDING] ?? 0),
                        (int) ($counts[Community::VERIFICATION_NEEDS_REVIEW] ?? 0),
                        (int) ($counts[Community::VERIFICATION_REJECTED] ?? 0),
                    ],
                    'backgroundColor' => ['#059669', '#d97706', '#0284c7', '#dc2626'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['تأییدشده', 'در انتظار', 'نیازمند بازبینی', 'ردشده'],
        ];
    }
}
