<?php

namespace App\Models;

use App\Models\Traits\AutoTranslate;
use App\Models\Traits\CanForceTranslates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Site extends Model
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasTranslations, CanForceTranslates, AutoTranslate;
    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'primary_domain',
        'extra_domains',
        'default_locale',
        'locales',
        'theme_key',
        'theme_overrides',
        'timezone',
        'contact_email',
        'feature_flags',
        'options',
        'team_meta',
        'owner_id',
        'header_menu_id',
        'footer_menu_id',
        'sidebar_menu_id'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'extra_domains' => 'array',
        'locales' => 'array',
        'theme_overrides' => 'array',
        'feature_flags' => 'array',
        'options' => 'array',
        'team_meta' => 'array',
        'auto_translate' => 'boolean',
        'i18n_overrides' => 'array',
    ];

    public function lockedLocales(): array
    {
        return array_values($this->i18n_overrides['locked'] ?? []);
    }

    public array $translatable = [
        'slug',
        'team_meta',
    ];
// Media collections (logo, favicon, social image)
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('logo_dark')->singleFile();
        $this->addMediaCollection('favicon')->singleFile();
        $this->addMediaCollection('social_image_default')->singleFile();
    }
// Relationships
    public function pages()
    {
        return $this->hasMany(Page::class);
    }
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_key', 'key');
    }

    public function headerMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'header_menu_id');
    }

    public function footerMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'footer_menu_id');
    }

    public function sidebarMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'sidebar_menu_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'site_user_roles')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }
    public function ownedSites()
    {
        return $this->hasMany(Site::class, 'owner_id');
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'site_user_roles')
            ->withPivot('role')
            ->withTimestamps();
    }
}
