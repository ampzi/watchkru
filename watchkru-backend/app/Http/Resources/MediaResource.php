<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'original_title' => $this->original_title,
            'poster_path' => $this->poster_path,
            'backdrop_path' => $this->backdrop_path,
            'media_type' => $this->media_type,
            'release_year' => $this->release_year,
            'release_date' => $this->release_date?->toDateString(),
            'language' => $this->language,
            'status' => $this->status,
            'runtime' => $this->runtime,
            'number_of_seasons' => $this->number_of_seasons,
            'number_of_episodes' => $this->number_of_episodes,
            'director' => $this->director,
            'cast' => $this->cast,
            'overview' => $this->overview,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'communities_count' => $this->whenCounted('communities'),
        ];
    }
}
