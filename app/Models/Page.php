<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
