<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $title = fake()->sentence(2);
        $type = fake()->randomElement([Media::TYPE_MOVIE, Media::TYPE_SERIES]);

        return [
            'title' => $title,
            'original_title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'overview' => fake()->paragraph(),
            'poster_path' => null,
            'backdrop_path' => null,
            'media_type' => $type,
            'release_date' => fake()->dateTimeBetween('-15 years', 'now'),
            'release_year' => fake()->numberBetween(2010, 2026),
            'language' => 'en',
            'status' => $type === Media::TYPE_SERIES
                ? fake()->randomElement(['ongoing', 'ended', 'upcoming'])
                : 'released',
            'runtime' => $type === Media::TYPE_MOVIE ? fake()->numberBetween(85, 170) : null,
            'number_of_seasons' => $type === Media::TYPE_SERIES ? fake()->numberBetween(1, 6) : null,
            'number_of_episodes' => $type === Media::TYPE_SERIES ? fake()->numberBetween(6, 60) : null,
            'director' => fake()->name(),
            'cast' => [fake()->name(), fake()->name(), fake()->name()],
        ];
    }
}
