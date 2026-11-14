<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\CommunityReport;
use App\Models\CommunitySubmission;
use App\Support\AdminNotifier;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $pending = CommunitySubmission::query()
            ->where('status', CommunitySubmission::STATUS_PENDING)
            ->with('user')
            ->latest('id')
            ->first();

        if ($pending) {
            AdminNotifier::send(
                'ارسال در انتظار بررسی',
                'یک ارسال کاربر منتظر بررسی است.',
                url('/admin/community-submissions/'.$pending->id.'/edit'),
            );
        }

        $openReport = CommunityReport::query()
            ->where('status', CommunityReport::STATUS_OPEN)
            ->with('community')
            ->latest('id')
            ->first();

        if ($openReport) {
            AdminNotifier::send(
                'گزارش باز',
                "برای «{$openReport->community?->name}» یک گزارش باز وجود دارد.",
                url('/admin/community-reports/'.$openReport->id.'/edit'),
                'danger',
                'heroicon-o-flag',
            );
        }

        $verified = Community::query()->verified()->latest('id')->first();

        if ($verified) {
            AdminNotifier::send(
                'انجمن جدید تأیید شد',
                "«{$verified->name}» در فهرست عمومی نمایش داده می‌شود.",
                url('/admin/communities/'.$verified->id.'/edit'),
                'success',
                'heroicon-o-check-badge',
            );
        }
    }
}
