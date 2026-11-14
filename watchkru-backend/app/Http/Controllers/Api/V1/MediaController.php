<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        return $this->paginated($this->filtered($request)->paginate($this->perPage($request)), MediaResource::class);
    }

    public function movies(Request $request)
    {
        return $this->paginated(
            $this->filtered($request)->movies()->paginate($this->perPage($request)),
            MediaResource::class
        );
    }

    public function series(Request $request)
    {
        return $this->paginated(
            $this->filtered($request)->series()->paginate($this->perPage($request)),
            MediaResource::class
        );
    }

    public function show(Media $media)
    {
        $media->load('genres')->loadCount('communities');

        return $this->ok(new MediaResource($media));
    }

    protected function filtered(Request $request)
    {
        $query = Media::query()->withCount('communities');

        if ($type = $request->get('type')) {
            $query->where('media_type', $type);
        }

        if ($genre = $request->get('genre')) {
            $query->whereHas('genres', fn ($q) => $q->where('slug', $genre));
        }

        if ($year = $request->get('year')) {
            $query->where('release_year', $year);
        }

        if ($language = $request->get('language')) {
            $query->where('language', $language);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('original_title', 'like', "%{$q}%")
                    ->orWhereJsonContains('alternative_titles', $q);
            });
        }

        return match ($request->get('sort')) {
            'newest' => $query->orderByDesc('release_date'),
            'communities' => $query->orderByDesc('communities_count'),
            default => $query->orderByDesc('communities_count')->orderByDesc('release_date'), // popular
        };
    }

    protected function perPage(Request $request): int
    {
        return min((int) $request->get('per_page', 20), 50);
    }
}
