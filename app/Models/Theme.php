<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'version',
        'config_schema',
        'is_system',
        'notes',
    ];
    protected $casts = [
        'config_schema' => 'array',
        'is_system' => 'boolean',
    ];
}
