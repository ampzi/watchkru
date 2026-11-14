<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\CommunityReport;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Reports in every state (open, resolved, dismissed) so the admin queue,
 * filters and the dashboard's "open reports" stat are meaningful.
 */
class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where('role', User::ROLE_USER)->orderBy('id')->get();
        $admin = User::query()->where('email', 'admin@watchkru.test')->first();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($this->rows() as $index => $row) {
            $community = Community::query()->where('slug', $row['community'])->first();
            if (! $community) {
                continue;
            }

            $user = $users[$index % $users->count()];
            $isOpen = $row['status'] === CommunityReport::STATUS_OPEN;

            CommunityReport::updateOrCreate(
                [
                    'community_id' => $community->id,
                    'user_id' => $user->id,
                    'report_type' => $row['type'],
                ],
                [
                    'description' => $row['description'],
                    'status' => $row['status'],
                    'resolved_by' => $isOpen ? null : $admin?->id,
                    'resolved_at' => $isOpen ? null : now()->subDays($index + 2),
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function rows(): array
    {
        return [
            [
                'community' => 'the-100-wiki',
                'type' => 'wrong_description',
                'description' => 'توضیح نوشته‌شده دربارهٔ کتاب‌های اقتباسی کامل نیست؛ بهتر است به سه‌گانهٔ کتاب هم اشاره شود.',
                'status' => CommunityReport::STATUS_OPEN,
            ],
            [
                'community' => 'the-bear-reddit',
                'type' => 'inactive',
                'description' => 'به نظر می‌رسد بحث‌های انجمن از چند ماه پیش متوقف شده است.',
                'status' => CommunityReport::STATUS_OPEN,
            ],
            [
                'community' => 'the-100-instagram',
                'type' => 'broken_link',
                'description' => 'لینک صفحهٔ اینستاگرام باز نمی‌شود.',
                'status' => CommunityReport::STATUS_RESOLVED,
            ],
            [
                'community' => 'oppenheimer-reddit',
                'type' => 'wrong_platform',
                'description' => 'این مورد به‌جای ردیت باید در دستهٔ وب‌سایت ثبت شود.',
                'status' => CommunityReport::STATUS_DISMISSED,
            ],
        ];
    }
}
