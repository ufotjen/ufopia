<?php

namespace App\Models;

use App\Models\Traits\AutoTranslate;
use App\Models\Traits\CanForceTranslates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class UserProfile extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasTranslations, CanForceTranslates, AutoTranslate;

    protected $fillable = [
        'user_id',
        'username',
        'slug',
        'tagline',
        'bio',
        'is_profile_active',
        'social_links',
        'preferences',
    ];

    protected $casts = [
        'is_profile_active' => 'bool',
        'social_links' => 'array',
        'preferences' => 'array',
        'auto_translate' => 'boolean',
        'i18n_overrides' => 'array',
    ];

    public function lockedLocales(): array
    {
        return array_values($this->i18n_overrides['locked'] ?? []);
    }

    public array $translatable = [
        'tagline',
        'bio',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->useFallbackUrl('/images/avatar-fallback.png') // optioneel
            ->singleFile();

        $this->addMediaCollection('photos');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->nonQueued(); // of laat weg als je queue gebruikt
    }

    // relatie naar user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $profile) {
            if (blank($profile->slug)) {
                $profile->slug = Str::slug($profile->username);
            }
        });
    }

    // handige accessor
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb') ?: $this->getFirstMediaUrl('avatar');
    }
}
