<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\CommunityReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->orderBy('id')->take(6)->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($this->reviews() as $communitySlug => $reviews) {
            $community = Community::query()->where('slug', $communitySlug)->first();

            if (! $community) {
                continue;
            }

            foreach ($reviews as $index => [$rating, $content]) {
                $user = $users[$index % $users->count()];

                CommunityReview::updateOrCreate(
                    ['community_id' => $community->id, 'user_id' => $user->id],
                    ['rating' => $rating, 'content' => $content]
                );
            }
        }
    }

    /**
     * @return array<string, array<int, array{0: int, 1: string}>>
     */
    protected function reviews(): array
    {
        return [
            'the-100-reddit' => [
                [5, 'بهترین جای ردیت برای دنبال کردن The 100؛ بحث‌های قسمت‌به‌قسمت آرشیو شده‌اند و هنوز هم می‌شود دربارهٔ پایان فصل هفتم بحث کرد.'],
                [4, 'جامعهٔ فعالی است، ولی برای تازه‌واردها باید مراقب اسپویل‌های فصل‌های آخر بود.'],
                [4, 'مودریشن منظم و قوانین مشخص برای اسپویل؛ برای طرفدارهای قدیمی سریال جای خوبی است.'],
            ],
            'the-100-wiki' => [
                [5, 'هر جزئیاتی از سریال را می‌شود اینجا پیدا کرد؛ صفحهٔ قبیله‌ها و جدول زمانی وقایع واقعاً کارآمد است.'],
                [4, 'منبع خوبی برای نام شخصیت‌ها و قسمت‌هاست، هرچند بعضی صفحه‌ها به‌روزرسانی دیرهنگام دارند.'],
            ],
            'silo-reddit' => [
                [5, 'بهترین انجمن برای دنبال کردن Silo؛ بحث‌های فصل سوم و مقایسه با کتاب‌های هیو هوی بسیار خوب مدیریت می‌شود.'],
                [4, 'جامعهٔ صمیمی و کم‌اسپویل؛ برای کسانی که تازه سریال را شروع کرده‌اند هم امن است.'],
                [5, 'هفته‌به‌هفته همراه فصل سوم بودن با این انجمن تجربهٔ سریال را چند برابر کرد.'],
            ],
            'silo-apple-tv' => [
                [4, 'منبع رسمی و بدون شایعه؛ تاریخ دقیق فصل‌ها و تریلرها فقط از همین‌جا قابل اعتماد است.'],
            ],
            'hugh-howey-silo' => [
                [5, 'خواندن یادداشت‌های خود نویسنده دربارهٔ Wool و Shift دید تازه‌ای به سریال می‌دهد.'],
            ],
            'interstellar-reddit' => [
                [5, 'بحث‌های علمی انجمن دربارهٔ نسبیت و سیاه‌چاله‌ها واقعاً ارزش خواندن دارد.'],
                [4, 'برای طرفدارهای نولان جای خوبی است، هرچند بحث‌ها گاهی تکراری می‌شوند.'],
            ],
            'the-bear-reddit' => [
                [4, 'بحث قسمت‌به‌قسمت خوبی دارد و آشپزهای حرفه‌ای هم در آن نکات فنی می‌گویند.'],
            ],
            'dune-reddit' => [
                [5, 'جامع‌ترین انجمن تل‌ماسه؛ از کتاب اصلی تا فیلم‌های ویلنوو همه‌چیز پوشش داده می‌شود.'],
            ],
            'true-detective-reddit' => [
                [4, 'تحلیل هر فصل با جزئیات انجام می‌شود؛ برای فصل چهارم هم بحث‌های خوبی داشت.'],
            ],
            'oppenheimer-reddit' => [
                [4, 'بحث‌های تاریخی و فنی خوبی دربارهٔ پروژهٔ منهتن و ساخت فیلم دارد.'],
            ],
        ];
    }
}
