<?php

namespace Database\Seeders;

use App\Models\CommunityCategory;
use Illuminate\Database\Seeder;

class CommunityCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'news' => ['name' => 'اخبار', 'description' => 'تازه‌ترین خبرهای مرتبط با فیلم یا سریال.'],
            'discussion' => ['name' => 'گفت‌وگو', 'description' => 'بحث آزاد درباره داستان و شخصیت‌ها.'],
            'fan-community' => ['name' => 'انجمن طرفداران', 'description' => 'جامعه طرفداران یک اثر خاص.'],
            'downloads' => ['name' => 'دانلود', 'description' => 'لینک‌های دانلود یا تماشای آنلاین.'],
            'reviews' => ['name' => 'نقد و بررسی', 'description' => 'نقد و تحلیل فیلم و سریال.'],
            'fan-theories' => ['name' => 'تئوری‌های طرفداران', 'description' => 'حدس و گمان درباره ادامه داستان.'],
            'memes' => ['name' => 'میم و طنز', 'description' => 'محتوای طنز مرتبط با اثر.'],
            'watch-together' => ['name' => 'تماشای گروهی', 'description' => 'هماهنگی برای تماشای هم‌زمان.'],
            'general-entertainment' => ['name' => 'سرگرمی عمومی', 'description' => 'محتوای عمومی سینما و تلویزیون.'],
        ];

        foreach ($categories as $slug => $data) {
            CommunityCategory::firstOrCreate(['slug' => $slug], $data);
        }
    }
}
