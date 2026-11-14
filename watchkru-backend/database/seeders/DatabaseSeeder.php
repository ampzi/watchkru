<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Reference data first — everything else resolves these by slug.
            GenreSeeder::class,
            PlatformSeeder::class,
            CommunityCategorySeeder::class,
            UserSeeder::class,
            MediaSeeder::class,
            SeriesCatalogSeeder::class,
            CommunitySeeder::class,
            // User activity: reviews, moderation queue and reports.
            ReviewSeeder::class,
            SubmissionSeeder::class,
            ReportSeeder::class,
            FavoriteSeeder::class,
            ChatSeeder::class,
            // Last: turns the activity above into panel notifications.
            NotificationSeeder::class,
        ]);
    }
}
