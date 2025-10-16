<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    /** @use HasFactory<\Database\Factories\MenuItemFactory> */
    use HasFactory;
    use SoftDeletes;
    use HasTranslations;
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
    ];
    public $translatable = [
        'label',
    ];
    protected $casts = [
        'label' => 'array',
        'route_params' => 'array',
        'visible' => 'boolean',
        'roles_visible' => 'array',
    ];
    public function menu()
    {
        return $this->belongsTo(Menu::class);
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
}
