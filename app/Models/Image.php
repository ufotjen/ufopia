<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

/**
 * @method void prepareToAttachMedia(Media $media, FileAdder $fileAdder)
 */
class Image extends Model
{
    use HasTags; // maakt $image->tags() mogelijk (via spatie/tags morph)

    protected $guarded = [];

    protected $casts = [
        'is_main' => 'boolean',
        'extra'   => 'array',
    ];
}
