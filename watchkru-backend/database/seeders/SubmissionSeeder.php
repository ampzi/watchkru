<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\CommunityCategory;
use App\Models\CommunitySubmission;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Example submissions so the moderation queue, the dashboard counters and the
 * notification bell have realistic content: two pending, one approved, one
 * rejected. Payloads mirror StoreCommunityRequest.
 */
class SubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where('role', User::ROLE_USER)->orderBy('id')->get();
        $categories = CommunityCategory::query()->pluck('id', 'slug');
        $admin = User::query()->where('email', 'admin@watchkru.test')->first();

        if ($users->count() < 4) {
            return;
        }

        foreach ($this->rows() as $index => $row) {
            $media = Media::query()->where('slug', $row['media'])->first();
            if (! $media) {
                continue;
            }

            $user = $users[$index % $users->count()];
            $categoryId = $categories[$row['category']] ?? $categories->first();
            $community = isset($row['community']) ? Community::query()->where('slug', $row['community'])->first() : null;

            CommunitySubmission::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'media_id' => $media->id,
                ],
                [
                    'community_id' => $community?->id,
                    'submission_data' => [
                        'name' => $row['name'],
                        'platform' => $row['platform'],
                        'external_url' => $row['url'],
                        'media_id' => $media->id,
                        'community_category_id' => $categoryId,
                        'language' => 'fa',
                        'description' => $row['description'],
                        'member_count' => $row['member_count'] ?? null,
                    ],
                    'status' => $row['status'],
                    'admin_note' => $row['admin_note'] ?? null,
                    'reviewed_by' => $row['status'] === CommunitySubmission::STATUS_PENDING ? null : $admin?->id,
                    'reviewed_at' => $row['status'] === CommunitySubmission::STATUS_PENDING ? null : now()->subDays($index + 1),
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function rows(): array
    {
        return [
            [
                'media' => 'silo',
                'name' => 'گروه تلگرام سایلو — فارسی',
                'platform' => 'telegram_group',
                'url' => 'https://t.me/silo_fa_discussion',
                'category' => 'fan-community',
                'description' => 'گروه گفت‌وگوی فارسی‌زبان طرفداران Silo؛ هر قسمت با هم تماشا و بعد از آن بحث می‌شود. قوانین اسپویل سخت‌گیرانه است.',
                'member_count' => 1240,
                'status' => CommunitySubmission::STATUS_PENDING,
            ],
            [
                'media' => 'the-100',
                'name' => 'کانال تلگرام اخبار The 100',
                'platform' => 'telegram_channel',
                'url' => 'https://t.me/the100_fa_news',
                'category' => 'news',
                'description' => 'اخبار سریال، تریلرها و یادآوری تاریخ پخش قسمت‌ها برای بینندگان فارسی‌زبان.',
                'member_count' => 830,
                'status' => CommunitySubmission::STATUS_PENDING,
            ],
            [
                'media' => 'silo',
                'community' => 'silo-reddit',
                'name' => 'r/SiloSeries — ردیت',
                'platform' => 'reddit_community',
                'url' => 'https://www.reddit.com/r/SiloSeries/',
                'category' => 'fan-community',
                'description' => 'انجمن ردیت سایلو، پیشنهادشده برای فهرست فارسی.',
                'member_count' => 78000,
                'status' => CommunitySubmission::STATUS_APPROVED,
                'admin_note' => 'لینک معتبر است و در فهرست تأییدشده قرار گرفت.',
            ],
            [
                'media' => 'the-100',
                'name' => 'پیج اینستاگرام فن‌سایت صد',
                'platform' => 'instagram_page',
                'url' => 'https://instagram.com/the100_fan_fa',
                'category' => 'reviews',
                'description' => 'پیج فن‌سایت با ادیت و کلیپ.',
                'status' => CommunitySubmission::STATUS_REJECTED,
                'admin_note' => 'صفحه غیرفعال است و لینک به محتوای مرتبط منتهی نمی‌شود.',
            ],
        ];
    }
}
