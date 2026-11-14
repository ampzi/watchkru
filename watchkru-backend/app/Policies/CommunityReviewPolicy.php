<?php

namespace App\Policies;

use App\Models\CommunityReview;
use App\Models\User;

class CommunityReviewPolicy
{
    public function update(User $user, CommunityReview $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function delete(User $user, CommunityReview $review): bool
    {
        return $user->id === $review->user_id || $user->isModerator();
    }
}
