<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\CommunityCategory;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CommunityFactory extends Factory
{
    protected $model = Community::class;

    public function definition(): array
    {
        $name = fake()->company().' '.fake()->randomElement(['Fans', 'Discussion', 'Community']);
        $url = 'https://t.me/'.Str::slug($name);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->sentence(12),
            'external_url' => $url,
            'normalized_url' => Community::normalizeUrl($url),
            // Fall back to creating the related row so the factory works on a
            // fresh database (tests run without running the seeders).
            'platform_id' => Platform::query()->inRandomOrder()->value('id') ?? Platform::factory(),
            'community_category_id' => CommunityCategory::query()->inRandomOrder()->value('id') ?? CommunityCategory::factory(),
            'language' => 'fa',
            'member_count' => fake()->numberBetween(50, 20000),
            'member_count_verified_at' => now(),
            'activity_status' => fake()->randomElement(['active', 'inactive', 'unknown']),
            'verification_status' => Community::VERIFICATION_VERIFIED,
            'last_checked_at' => now(),
            'submission_source' => 'seed',
        ];
    }
}
