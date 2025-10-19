<?php

namespace App\Models;

use App\Models\Traits\AutoTranslate;
use App\Models\Traits\CanForceTranslates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\MenuItemFactory> */
    use HasFactory;
    use SoftDeletes;
    use HasTranslations, InteractsWithMedia, AutoTranslate, CanForceTranslates;
    protected $fillable = [
        'menu_id',
        'parent_id',
        'type',
        'label',
        'url',
        'page_id',
        'route_name',
        'route_params',
        'target',
        'icon',
        'visible',
        'roles_visible',
        'sort_order',
        'icon_mode', 'icon_class', 'auto_translate', 'i18n_overrides',
    ];
    public array $translatable = [
        'label',
    ];
    protected $casts = [
        'route_params' => 'array',
        'visible' => 'boolean',
        'roles_visible' => 'array',
        'auto_translate' => 'boolean',
        'i18n_overrides' => 'array',
    ];
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function lockedLocales(): array
    {
        return array_values($this->i18n_overrides['locked'] ?? []);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(64)->height(64)->performOnCollections('icon');
    }

    // (optioneel)
    public function getIconUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('icon', 'thumb') ?: $this->getFirstMediaUrl('icon');
    }
}
