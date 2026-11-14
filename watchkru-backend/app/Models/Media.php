<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_MOVIE = 'movie';
    public const TYPE_SERIES = 'series';

    protected $fillable = [
        'title',
        'original_title',
        'alternative_titles',
        'slug',
        'overview',
        'poster_path',
        'backdrop_path',
        'media_type',
        'release_date',
        'release_year',
        'language',
        'status',
        'runtime',
        'number_of_seasons',
        'number_of_episodes',
        'director',
        'cast',
        'external_provider',
        'external_id',
    ];

    protected function casts(): array
    {
        return [
            'alternative_titles' => 'array',
            'cast' => 'array',
            'release_date' => 'date',
        ];
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'genre_media');
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'community_media')
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CommunitySubmission::class);
    }

    public function scopeMovies($query)
    {
        return $query->where('media_type', self::TYPE_MOVIE);
    }

    public function scopeSeries($query)
    {
        return $query->where('media_type', self::TYPE_SERIES);
    }
}
