<?php

namespace App\Models;

use App\Enums\MenuLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Menu extends Model
{
    /** @use HasFactory<\Database\Factories\MenuFactory> */
    use HasFactory, HasTranslations;

    protected $fillable = [
        'site_id',
        'key',
        'title',
        'is_active',
        'slug'
    ];

    public array $translatable = ['title', 'slug'];
    protected $casts = [
        'title' => 'string',
        'is_active' => 'boolean',
        'slug' => 'string',
        'key' => MenuLocation::class,
        'auto_translate' => 'boolean',
        'i18n_overrides' => 'array',
    ];

    public function lockedLocales(): array
    {
        return array_values($this->i18n_overrides['locked'] ?? []);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->orderBy('sort_order');
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'page_menu')
            ->using(PageMenu::class)
            ->withPivot('location')
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        static::saving(function (Menu $menu) {
            // Als slug leeg is, genereer een basis voor de fallback-locale
            if (blank($menu->slug)) {
                $fallback = config('app.fallback_locale', 'en');

                // Titel prefereren (fallback-locale), anders 1e beschikbare waarde
                $title = $menu->getTranslation('title', $fallback);
                if (!$title) {
                    $arr = is_array($menu->title) ? $menu->title : [];
                    $title = $arr[$fallback] ?? (reset($arr) ?: null);
                }

                // Enum → string value
                $keyString = $menu->key instanceof \BackedEnum ? $menu->key->value : (string) $menu->key;

                $base = $title ?: $keyString ?: 'menu';
                $slug = Str::slug($base);

                // Zet expliciet de vertaling voor de fallback-locale
                $menu->setTranslation('slug', $fallback, $slug);
            }
        });
    }
}
