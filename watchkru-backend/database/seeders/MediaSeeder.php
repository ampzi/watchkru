<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Media;
use Illuminate\Database\Seeder;

/**
 * Curated, real metadata for a handful of well-known titles.
 *
 * Facts (air dates, season/episode counts, creators and main cast) follow the
 * titles' public records as of November 2026. No random/fake titles are
 * generated here — the directory should never show invented catalog entries.
 */
class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $genreIds = Genre::query()->pluck('id', 'slug');

        foreach ($this->titles() as $title) {
            $genreSlugs = $title['genres'];
            unset($title['genres']);

            $media = Media::updateOrCreate(['slug' => $title['slug']], $title);

            $media->genres()->sync(
                collect($genreSlugs)
                    ->map(fn (string $slug) => $genreIds[$slug] ?? null)
                    ->filter()
                    ->values()
                    ->all()
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function titles(): array
    {
        return [
            [
                'slug' => 'the-100',
                'title' => 'The 100',
                'original_title' => 'The 100',
                'alternative_titles' => ['صد نفر', '۱۰۰', 'The Hundred'],
                'overview' => 'نودوهفت سال پس از جنگ هسته‌ای که زمین را غیرقابل سکونت کرد، بازماندگان بشریت در ایستگاه فضایی «آرک» زندگی می‌کنند. با پایان یافتن منابع حیاتی ایستگاه، صد نوجوان محکوم به‌عنوان نخستین گروه به زمین فرستاده می‌شوند تا ببینند سیاره دیگر قابل زندگی است یا نه. آنچه در زمین پیدا می‌کنند بسیار پیچیده‌تر از یک زمین سوخته است.',
                'media_type' => Media::TYPE_SERIES,
                'release_date' => '2014-03-19',
                'release_year' => 2014,
                'language' => 'en',
                'status' => 'ended',
                'runtime' => 43,
                'number_of_seasons' => 7,
                'number_of_episodes' => 100,
                'director' => 'Jason Rothenberg',
                'cast' => [
                    'Eliza Taylor',
                    'Bob Morley',
                    'Marie Avgeropoulos',
                    'Paige Turco',
                    'Isaiah Washington',
                    'Henry Ian Cusick',
                    'Lindsey Morgan',
                    'Richard Harmon',
                    'Tasya Teles',
                    'Christopher Larkin',
                ],
                'external_provider' => 'tmdb',
                'external_id' => '48866',
                'poster_path' => '/images/posters/the-100.jpg',
                'genres' => ['sci-fi', 'drama', 'mystery', 'action', 'adventure'],
            ],
            [
                'slug' => 'silo',
                'title' => 'Silo',
                'original_title' => 'Silo',
                'alternative_titles' => ['سیلو', 'Silo (Apple TV+)'],
                'overview' => 'در آینده‌ای دور، ده‌هزار نفر در یک سیلوی عظیم زیرزمینی زندگی می‌کنند؛ جامعه‌ای بسته با قوانینی که پرسیدن درباره «بیرون» را جرم می‌داند. ژولیت نیکولز، مهندس مکانیک، پس از مرگ مرموز یکی از همکارانش در جست‌وجوی حقیقت به لایه‌های پنهان سیلو می‌رسد. سریال اقتباسی از سه‌گانه «سیلو» نوشته هیو هوی است.',
                'media_type' => Media::TYPE_SERIES,
                'release_date' => '2023-05-05',
                'release_year' => 2023,
                'language' => 'en',
                'status' => 'ongoing',
                'runtime' => 50,
                'number_of_seasons' => 3,
                'number_of_episodes' => 30,
                'director' => 'Graham Yost',
                'cast' => [
                    'Rebecca Ferguson',
                    'Common',
                    'Tim Robbins',
                    'Harriet Walter',
                    'Chinaza Uche',
                    'Steve Zahn',
                    'Avi Nash',
                    'Billy Postlethwaite',
                ],
                'external_provider' => 'tmdb',
                'external_id' => '125988',
                'poster_path' => '/images/posters/silo.jpg',
                'genres' => ['sci-fi', 'drama', 'mystery'],
            ],
            [
                'slug' => 'interstellar',
                'title' => 'Interstellar',
                'original_title' => 'Interstellar',
                'alternative_titles' => ['میان‌ستاره‌ای'],
                'overview' => 'در آینده‌ای نزدیک که زمین در حال نابودی است، گروهی از فضانوردان از میان کرم‌چاله‌ای در نزدیکی زحل عبور می‌کنند تا خانه‌ای تازه برای بشریت بیابند. کوپر، خلبان سابق ناسا، در این سفر باید میان نجات خانواده‌اش و نجات نوع بشر یکی را انتخاب کند.',
                'media_type' => Media::TYPE_MOVIE,
                'release_date' => '2014-11-07',
                'release_year' => 2014,
                'language' => 'en',
                'status' => 'released',
                'runtime' => 169,
                'number_of_seasons' => null,
                'number_of_episodes' => null,
                'director' => 'Christopher Nolan',
                'cast' => [
                    'Matthew McConaughey',
                    'Anne Hathaway',
                    'Jessica Chastain',
                    'Michael Caine',
                    'Matt Damon',
                ],
                'external_provider' => 'tmdb',
                'external_id' => '157336',
                'poster_path' => '/images/posters/interstellar.jpg',
                'genres' => ['sci-fi', 'drama', 'adventure'],
            ],
            [
                'slug' => 'the-bear',
                'title' => 'The Bear',
                'original_title' => 'The Bear',
                'alternative_titles' => ['خرس'],
                'overview' => 'کارمی برزاتو، سرآشپز جوان رستوران‌های سطح بالای دنیای آشپزی، پس از مرگ برادرش به شیکاگو بازمی‌گردد تا ساندویچ‌فروشی خانوادگی «The Original Beef» را اداره کند و در همین مسیر با خانواده و گذشته خودش روبه‌رو می‌شود.',
                'media_type' => Media::TYPE_SERIES,
                'release_date' => '2022-06-23',
                'release_year' => 2022,
                'language' => 'en',
                'status' => 'ongoing',
                'runtime' => 30,
                'number_of_seasons' => 4,
                'number_of_episodes' => 38,
                'director' => 'Christopher Storer',
                'cast' => [
                    'Jeremy Allen White',
                    'Ayo Edebiri',
                    'Ebon Moss-Bachrach',
                    'Liza Colón-Zayas',
                    'Abby Elliott',
                    'Matty Matheson',
                ],
                'external_provider' => 'tmdb',
                'external_id' => '136315',
                'poster_path' => '/images/posters/the-bear.jpg',
                'genres' => ['drama', 'comedy'],
            ],
            [
                'slug' => 'dune-part-two',
                'title' => 'Dune: Part Two',
                'original_title' => 'Dune: Part Two',
                'alternative_titles' => ['تل‌ماسه: بخش دوم'],
                'overview' => 'پل آترایدیس پس از سقوط خاندانش به فرمن‌ها می‌پیوندد و در کنار چانی مسیر انتقام از توطئه‌گران را در پیش می‌گیرد، در حالی که آیندهٔ آراکیس و کهکشان به انتخاب‌های او گره خورده است.',
                'media_type' => Media::TYPE_MOVIE,
                'release_date' => '2024-03-01',
                'release_year' => 2024,
                'language' => 'en',
                'status' => 'released',
                'runtime' => 166,
                'number_of_seasons' => null,
                'number_of_episodes' => null,
                'director' => 'Denis Villeneuve',
                'cast' => [
                    'Timothée Chalamet',
                    'Zendaya',
                    'Rebecca Ferguson',
                    'Javier Bardem',
                    'Austin Butler',
                    'Florence Pugh',
                ],
                'external_provider' => 'tmdb',
                'external_id' => '693134',
                'poster_path' => '/images/posters/dune-part-two.jpg',
                'genres' => ['sci-fi', 'adventure', 'action'],
            ],
            [
                'slug' => 'true-detective',
                'title' => 'True Detective',
                'original_title' => 'True Detective',
                'alternative_titles' => ['کارآگاه حقیقی'],
                'overview' => 'مجموعه‌ای آنتولوژیک که در هر فصل پرونده‌ای جنایی را در جغرافیایی متفاوت دنبال می‌کند؛ از لوئیزیانای فصل اول تا آلاسکای فصل چهارم. تمرکز اصلی سریال بر رابطهٔ دو کارآگاه و هزینه‌های روانی این کار است.',
                'media_type' => Media::TYPE_SERIES,
                'release_date' => '2014-01-12',
                'release_year' => 2014,
                'language' => 'en',
                'status' => 'ongoing',
                'runtime' => 55,
                'number_of_seasons' => 4,
                'number_of_episodes' => 30,
                'director' => 'Nic Pizzolatto',
                'cast' => [
                    'Matthew McConaughey',
                    'Woody Harrelson',
                    'Mahershala Ali',
                    'Jodie Foster',
                ],
                'external_provider' => 'tmdb',
                'external_id' => '46648',
                'poster_path' => '/images/posters/true-detective.jpg',
                'genres' => ['crime', 'drama', 'mystery'],
            ],
            [
                'slug' => 'oppenheimer',
                'title' => 'Oppenheimer',
                'original_title' => 'Oppenheimer',
                'alternative_titles' => ['اوپنهایمر'],
                'overview' => 'روایت زندگی جی. رابرت اوپنهایمر، فیزیکدانی که رهبری پروژهٔ منهتن و ساخت نخستین بمب اتمی را بر عهده داشت؛ از سال‌های تحصیل و تدریس تا آزمون ترینیتی و دادگاه‌های پس از جنگ.',
                'media_type' => Media::TYPE_MOVIE,
                'release_date' => '2023-07-21',
                'release_year' => 2023,
                'language' => 'en',
                'status' => 'released',
                'runtime' => 180,
                'number_of_seasons' => null,
                'number_of_episodes' => null,
                'director' => 'Christopher Nolan',
                'cast' => [
                    'Cillian Murphy',
                    'Emily Blunt',
                    'Robert Downey Jr.',
                    'Matt Damon',
                    'Florence Pugh',
                ],
                'external_provider' => 'tmdb',
                'external_id' => '872585',
                'poster_path' => '/images/posters/oppenheimer.jpg',
                'genres' => ['drama'],
            ],
        ];
    }
}
