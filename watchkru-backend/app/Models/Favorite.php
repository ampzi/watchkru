<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Polymorphic favorite: favoritable_type is one of Media::class or
 * Community::class, enforced in UserController rather than at the DB layer
 * (Laravel morph maps keep this safe against class-name changes).
 */
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'favoritable_type', 'favoritable_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }
}
