<?php

namespace App\Filament\Widgets;

use App\Models\Community;
use App\Models\CommunityReport;
use App\Models\CommunitySubmission;
use App\Models\Media;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->subMonths($offset)->format('Y-m'));

        $mediaTrend = $months->map(fn (string $month) => Media::query()
            ->whereBetween('created_at', ["{$month}-01 00:00:00", "{$month}-31 23:59:59"])
            ->count())->all();

        $communityTrend = $months->map(fn (string $month) => Community::query()
            ->whereBetween('created_at', ["{$month}-01 00:00:00", "{$month}-31 23:59:59"])
            ->count())->all();

        $userTrend = $months->map(fn (string $month) => User::query()
            ->whereBetween('created_at', ["{$month}-01 00:00:00", "{$month}-31 23:59:59"])
            ->count())->all();

        return [
            Stat::make('عنوان‌ها', Media::query()->count())
                ->description('فیلم و سریال در آرشیو')
                ->descriptionIcon(Heroicon::OutlinedFilm)
                ->chart($mediaTrend)
                ->color('warning'),

            Stat::make('انجمن‌ها', Community::query()->count())
                ->description('تأییدشده: '.Community::query()->verified()->count())
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->chart($communityTrend)
                ->color('success'),

            Stat::make('کاربران', User::query()->count())
                ->description('عضو ثبت‌نام‌شده')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->chart($userTrend)
                ->color('info'),

            Stat::make('در انتظار بررسی', CommunitySubmission::query()->where('status', CommunitySubmission::STATUS_PENDING)->count())
                ->description('ارسال‌های کاربران')
                ->descriptionIcon(Heroicon::OutlinedInboxArrowDown)
                ->color('warning'),

            Stat::make('گزارش‌های باز', CommunityReport::query()->where('status', CommunityReport::STATUS_OPEN)->count())
                ->description('نیازمند رسیدگی')
                ->descriptionIcon(Heroicon::OutlinedFlag)
                ->color('danger'),
        ];
    }
}
