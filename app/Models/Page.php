<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasTranslations;

    protected $fillable = [
        'site_id',
        'parent_id',
        'path',
        'slug',
        'title',
        'excerpt',
        'content',
        'template',
        'status',
        'published_at',
        'noindex',
        'canonical_url',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_homepage',
    ];
    public $translatable = [
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];
    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
        'content' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'noindex' => 'boolean',
        'published_at' => 'datetime',
        'is_homepage' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            // zorg dat slug bestaat
            if (blank($page->slug) && filled($page->title)) {
                $page->slug = \Illuminate\Support\Str::slug(is_array($page->title) ? ($page->title['en'] ?? reset($page->title)) : $page->title);
            }

            // bouw path: parent/path + slug (zonder leading slash)
            $prefix = trim(optional($page->parent)->path ?? '', '/');
            $page->path = trim($prefix ? "{$prefix}/{$page->slug}" : $page->slug, '/');
        });

        static::creating(function (Page $page) {
            if (auth()->check()) {
                $page->author_id ??= auth()->id();
            }
        });

        static::updating(function (Page $page) {
            if (auth()->check()) {
                $page->editor_id = auth()->id();
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('og_image')->singleFile();
        $this->addMediaCollection('gallery');
    }

// Relationships
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');

    }

    public function extraMenus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'page_menu')
            ->using(PageMenu::class)
            ->withPivot('location') // als je locaties gebruikt
            ->withTimestamps();
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

    // Helpers: effectieve keuzes (override > site-default)
    public function effectiveHeaderMenu(): ?Menu
    {
        return $this->headerMenu ?: $this->site?->headerMenu;
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'page_menu', 'page_id', 'menu_id')
            ->withPivot(['location'])   // haal weg als je (nog) geen 'location' kolom hebt
            ->withTimestamps();
    }

// (handig, optioneel)
    public function menu(string $location): Model
    {
        return $this->menus()->wherePivot('location', $location)->first();
    }

    public function effectiveFooterMenu(): ?Menu
    {
        return $this->footerMenu ?: $this->site?->footerMenu;
    }

    public function effectiveSidebarMenu(): ?Menu
    {
        return $this->sidebarMenu ?: $this->site?->sidebarMenu;
    }

    public function author(): BelongsTo
    { return $this->belongsTo(User::class, 'author_id'); }
    public function editor(): BelongsTo
    { return $this->belongsTo(User::class, 'editor_id'); }

}
