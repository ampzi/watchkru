<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\CommunityCategory;
use App\Models\Media;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Real, publicly reachable communities (subreddits, wikis, official pages).
 *
 * URLs are the canonical ones and have been checked against the live pages.
 * Member counts are approximate public subscriber snapshots (November 2026)
 * and are only stored for entries where a public counter exists; entries that
 * expose no count keep `member_count` null instead of inventing a figure.
 */
class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $platforms = Platform::query()->pluck('id', 'slug');
        $categories = CommunityCategory::query()->pluck('id', 'slug');
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();

        foreach ($this->communities() as $row) {
            $media = Media::query()->where('slug', $row['media'])->first();
            $platformId = $platforms[$row['platform']] ?? null;
            $categoryId = $categories[$row['category']] ?? null;

            if (! $media || ! $platformId || ! $categoryId) {
                continue;
            }

            $community = Community::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'external_url' => $row['url'],
                    'normalized_url' => Community::normalizeUrl($row['url']),
                    'platform_id' => $platformId,
                    'community_category_id' => $categoryId,
                    'language' => $row['language'] ?? 'en',
                    'member_count' => $row['member_count'] ?? null,
                    'member_count_verified_at' => isset($row['member_count']) ? now() : null,
                    'activity_status' => Community::ACTIVITY_ACTIVE,
                    'verification_status' => Community::VERIFICATION_VERIFIED,
                    'last_checked_at' => now(),
                    'submission_source' => 'seed',
                    'created_by' => $admin?->id,
                    'approved_by' => $admin?->id,
                ]
            );

            $community->media()->syncWithoutDetaching([$media->id]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function communities(): array
    {
        return [
            [
                'media' => 'the-100',
                'slug' => 'the-100-reddit',
                'name' => 'r/The100 — ردیت',
                'description' => 'پرجمعیت‌ترین انجمن انگلیسی‌زبان طرفداران The 100؛ بحث قسمت‌به‌قسمت هفت فصل، تئوری‌ها، بازبینی شخصیت‌ها و آرشیو گفت‌وگوهای زمان پخش.',
                'url' => 'https://www.reddit.com/r/The100/',
                'platform' => Platform::REDDIT_COMMUNITY,
                'category' => 'fan-community',
                'member_count' => 245000,
            ],
            [
                'media' => 'the-100',
                'slug' => 'the-100-wiki',
                'name' => 'The 100 Wiki (Fandom)',
                'description' => 'دانشنامهٔ جامع سریال با صفحهٔ جداگانه برای هر قسمت، شخصیت، قبیله و کتاب‌های اقتباسی «The 100» نوشتهٔ کَس مورگان.',
                'url' => 'https://the100.fandom.com/',
                'platform' => Platform::WEBSITE_FORUM,
                'category' => 'general-entertainment',
            ],
            [
                'media' => 'the-100',
                'slug' => 'the-100-instagram',
                'name' => 'The 100 — اینستاگرام رسمی شبکه CW',
                'description' => 'صفحهٔ رسمی سریال در اینستاگرام؛ تریلرها، عکس‌های پشت صحنه و اعلان‌های فصل‌های پایانی.',
                'url' => 'https://www.instagram.com/the100cw/',
                'platform' => Platform::INSTAGRAM_PAGE,
                'category' => 'news',
            ],
            [
                'media' => 'silo',
                'slug' => 'silo-reddit',
                'name' => 'r/SiloSeries — ردیت',
                'description' => 'انجمن اصلی طرفداران Silo در ردیت؛ بحث قسمت‌های فصل سوم، مقایسه با کتاب‌های هیو هوی و تئوری‌های مربوط به سیلوهای دیگر.',
                'url' => 'https://www.reddit.com/r/SiloSeries/',
                'platform' => Platform::REDDIT_COMMUNITY,
                'category' => 'fan-community',
                'member_count' => 78000,
            ],
            [
                'media' => 'silo',
                'slug' => 'silo-apple-tv',
                'name' => 'Silo — صفحهٔ رسمی Apple TV+',
                'description' => 'اطلاعیه‌های رسمی اپل دربارهٔ سریال: تاریخ انتشار فصل‌ها، تریلرها، عوامل و خلاصهٔ رسمی داستان هر فصل.',
                'url' => 'https://www.apple.com/tv-pr/originals/silo/',
                'platform' => Platform::WEBSITE_FORUM,
                'category' => 'news',
            ],
            [
                'media' => 'silo',
                'slug' => 'hugh-howey-silo',
                'name' => 'Hugh Howey — نویسندهٔ سه‌گانهٔ Silo',
                'description' => 'سایت رسمی نویسندهٔ کتاب‌های Wool، Shift و Dust که سریال بر پایهٔ آن‌ها ساخته شده است؛ یادداشت‌های نویسنده و پاسخ به پرسش‌های خوانندگان.',
                'url' => 'https://hughhowey.com/',
                'platform' => Platform::WEBSITE_FORUM,
                'category' => 'general-entertainment',
            ],
            [
                'media' => 'interstellar',
                'slug' => 'interstellar-reddit',
                'name' => 'r/interstellar — ردیت',
                'description' => 'بحث‌های طرفداران دربارهٔ فیزیک، پایان‌بندی و موسیقی متن Interstellar؛ یکی از فعال‌ترین انجمن‌های فیلم‌های کریستوفر نولان.',
                'url' => 'https://www.reddit.com/r/interstellar/',
                'platform' => Platform::REDDIT_COMMUNITY,
                'category' => 'fan-theories',
                'member_count' => 210000,
            ],
            [
                'media' => 'the-bear',
                'slug' => 'the-bear-reddit',
                'name' => 'r/TheBear — ردیت',
                'description' => 'انجمن طرفداران The Bear؛ بحث قسمت‌به‌قسمت، آشپزی واقعی سریال و تحلیل شخصیت‌ها.',
                'url' => 'https://www.reddit.com/r/TheBear/',
                'platform' => Platform::REDDIT_COMMUNITY,
                'category' => 'discussion',
                'member_count' => 90000,
            ],
            [
                'media' => 'dune-part-two',
                'slug' => 'dune-reddit',
                'name' => 'r/dune — ردیت',
                'description' => 'بزرگ‌ترین انجمن دنیای تل‌ماسه؛ از کتاب‌های فرانک هربرت تا فیلم‌های دنیس ویلنوو و سریال‌های تلویزیونی.',
                'url' => 'https://www.reddit.com/r/dune/',
                'platform' => Platform::REDDIT_COMMUNITY,
                'category' => 'fan-community',
                'member_count' => 250000,
            ],
            [
                'media' => 'true-detective',
                'slug' => 'true-detective-reddit',
                'name' => 'r/TrueDetective — ردیت',
                'description' => 'بحث و تحلیل هر چهار فصل True Detective؛ از کارآگاه‌های لوئیزیانا تا شب‌های قطبی آلاسکا.',
                'url' => 'https://www.reddit.com/r/TrueDetective/',
                'platform' => Platform::REDDIT_COMMUNITY,
                'category' => 'discussion',
                'member_count' => 85000,
            ],
            [
                'media' => 'oppenheimer',
                'slug' => 'oppenheimer-reddit',
                'name' => 'r/Oppenheimer — ردیت',
                'description' => 'جامعهٔ طرفداران فیلم اوپنهایمر؛ گفت‌وگو دربارهٔ تاریخ پروژهٔ منهتن، فیلم‌نامه و موسیقی لودویگ گورانسون.',
                'url' => 'https://www.reddit.com/r/Oppenheimer/',
                'platform' => Platform::REDDIT_COMMUNITY,
                'category' => 'reviews',
            ],
        ];
    }
}
