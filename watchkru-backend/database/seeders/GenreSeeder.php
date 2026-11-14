<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Slugs are kept as stable, ASCII, English identifiers (used in query
     * params and URLs) while the display name is Persian — the same
     * pattern used for platforms and community categories below.
     */
    public function run(): void
    {
        $genres = [
            'sci-fi' => 'علمی‌تخیلی',
            'drama' => 'درام',
            'crime' => 'جنایی',
            'comedy' => 'کمدی',
            'action' => 'اکشن',
            'horror' => 'ترسناک',
            'fantasy' => 'فانتزی',
            'mystery' => 'معمایی',
            'romance' => 'عاشقانه',
            'animation' => 'انیمیشن',
            'documentary' => 'مستند',
            'adventure' => 'ماجراجویی',
        ];

        foreach ($genres as $slug => $name) {
            Genre::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
