<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    use HasFactory;

    public const TELEGRAM_GROUP = 'telegram_group';
    public const TELEGRAM_CHANNEL = 'telegram_channel';
    public const DISCORD_SERVER = 'discord_server';
    public const INSTAGRAM_PAGE = 'instagram_page';
    public const REDDIT_COMMUNITY = 'reddit_community';
    public const WHATSAPP_GROUP = 'whatsapp_group';
    public const WEBSITE_FORUM = 'website_forum';
    public const OTHER = 'other';

    protected $fillable = ['name', 'slug', 'icon'];

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }
}
