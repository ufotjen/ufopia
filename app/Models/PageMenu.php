<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageMenu extends Model
{
    protected $table = 'page_menu';
    protected $fillable = ['page_id', 'menu_id', 'location'];
}
