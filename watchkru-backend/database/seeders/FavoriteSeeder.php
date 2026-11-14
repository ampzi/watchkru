<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Favorite;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A few favorites per member so the user dashboard tab is not empty.
 */
class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where('role', User::ROLE_USER)->orderBy('id')->get();
        $media = Media::query()->orderBy('id')->take(4)->get();
        $communities = Community::query()->orderBy('id')->take(3)->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $index => $user) {
            foreach ($media->filter(fn ($_, $key) => ($key + $index) % 2 === 0) as $item) {
                Favorite::firstOrCreate([
                    'user_id' => $user->id,
                    'favoritable_type' => Media::class,
                    'favoritable_id' => $item->id,
                ]);
            }

            $community = $communities->get($index % max($communities->count(), 1));
            if ($community) {
                Favorite::firstOrCreate([
                    'user_id' => $user->id,
                    'favoritable_type' => Community::class,
                    'favoritable_id' => $community->id,
                ]);
            }
        }
    }
}
