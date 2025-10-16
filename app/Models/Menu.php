<?php

namespace App\Models;

use App\Enums\MenuLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    /** @use HasFactory<\Database\Factories\MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'site_id',
        'key',
        'title',
        'is_active',
        'slug'
    ];
    protected $casts = [
        'title' => 'array',
        'is_active' => 'boolean',
        'key' => MenuLocation::class,
    ];

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
            if (blank($menu->slug)) {
                $title = $menu->title;
                $base = is_array($title) ? ($title['en'] ?? reset($title)) : $title;
                $menu->slug = Str::slug((string) ($base ?: $menu->key ?: 'menu'));
            }
        });
    }
}
