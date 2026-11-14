<?php

namespace App\Services;

use App\Models\Community;
use Illuminate\Support\Collection;

/**
 * Flags likely-duplicate community submissions so a moderator can decide —
 * this never merges records automatically (brief: "Do not automatically
 * merge records based only on similar names").
 */
class DuplicateCommunityDetector
{
    public function findByUrl(string $url): ?Community
    {
        $normalized = Community::normalizeUrl($url);

        return Community::query()
            ->where('normalized_url', $normalized)
            ->first();
    }

    /**
     * Candidates with a similar name already linked to the same media item.
     * Pure heuristic signal for the admin UI — never auto-rejects.
     */
    public function findSimilarForMedia(string $name, int $mediaId): Collection
    {
        $needle = $this->normalizeName($name);

        return Community::query()
            ->whereHas('media', fn ($q) => $q->where('media.id', $mediaId))
            ->get()
            ->filter(fn (Community $c) => $this->similarity($needle, $this->normalizeName($c->name)) >= 0.72)
            ->values();
    }

    protected function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
    }

    protected function similarity(string $a, string $b): float
    {
        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $percent);

        return $percent / 100;
    }
}
