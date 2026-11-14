<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommunityResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\MediaResource;
use App\Models\Community;
use App\Models\Genre;
use App\Models\Media;
use Illuminate\Http\Request;

/**
 * Search works across Persian and English spellings of the same title.
 *
 * Beyond a plain LIKE, the query is normalised so that Persian/Arabic letter
 * variants (ي/ی، ك/ک، ة/ه…), the zero-width non-joiner and Persian digits all
 * match their Latin/standard forms. Matching is substring-based, so a title
 * is found from its first character or any character in the middle, and each
 * variant is tried against titles, original titles, alternative (Persian)
 * titles, slugs, cast and genre names.
 */
class SearchController extends Controller
{
    use ApiResponses;

    public function __invoke(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return $this->ok(['media' => [], 'communities' => [], 'genres' => []]);
        }

        $terms = $this->variants($q);
        $first = $terms[0];
        $year = ctype_digit($first) ? (int) $first : null;

        $media = Media::query()
            ->withCount('communities')
            ->where(function ($sub) use ($terms, $year) {
                foreach ($terms as $term) {
                    $like = "%{$term}%";

                    $sub->orWhere('title', 'like', $like)
                        ->orWhere('original_title', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhere('alternative_titles', 'like', $like)
                        ->orWhere('cast', 'like', $like)
                        ->orWhere('overview', 'like', $like)
                        ->orWhereHas('genres', fn ($g) => $g->where('name', 'like', $like));
                }

                if ($year !== null) {
                    $sub->orWhere('release_year', $year);
                }
            })
            // Titles that start with the query come first, then titles that
            // merely contain it; ties break on how many communities link to it.
            ->orderByRaw(
                'CASE WHEN title LIKE ? OR original_title LIKE ? OR alternative_titles LIKE ? THEN 0 ELSE 1 END',
                ["{$first}%", "{$first}%", "%{$first}%"]
            )
            ->orderByDesc('communities_count')
            ->orderBy('title')
            ->limit(12)
            ->get();

        $communities = Community::query()
            ->with(['platform', 'media'])
            ->verified()
            ->where(function ($sub) use ($terms) {
                foreach ($terms as $term) {
                    $like = "%{$term}%";
                    $sub->orWhere('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('media', fn ($m) => $m->where('title', 'like', $like));
                }
            })
            ->limit(10)
            ->get();

        $genres = Genre::query()
            ->where(function ($sub) use ($terms) {
                foreach ($terms as $term) {
                    $sub->orWhere('name', 'like', "%{$term}%");
                }
            })
            ->limit(6)
            ->get();

        return $this->ok([
            'query' => $q,
            'media' => MediaResource::collection($media),
            'communities' => CommunityResource::collection($communities),
            'genres' => GenreResource::collection($genres),
        ]);
    }

    /**
     * The original query plus normalised spellings, de-duplicated.
     *
     * @return array<int, string>
     */
    protected function variants(string $q): array
    {
        // Persian/Arabic letters that are typed interchangeably.
        $map = [
            'ي' => 'ی', 'ى' => 'ی', 'ئ' => 'ی',
            'ك' => 'ک',
            'ة' => 'ه', 'ۀ' => 'ه',
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
            'ؤ' => 'و',
            'ﻻ' => 'لا',
        ];

        // Persian and Arabic-Indic digits → ASCII.
        $digits = [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ];

        $normalized = strtr($q, $map + $digits);
        $normalized = str_replace(["\u{200c}", "\u{200d}", "\u{200e}", "\u{200f}"], '', $normalized);
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        // Also try the query without spaces (Persian users often join words)
        // and lower-cased for Latin titles.
        $variants = array_filter(array_unique([
            $q,
            $normalized,
            mb_strtolower($normalized),
            str_replace(' ', '', $normalized),
        ]));

        return array_values($variants);
    }
}
