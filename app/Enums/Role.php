<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Admin      = 'admin';
    case Editor     = 'editor';
    case Moderator  = 'moderator';
    case Author     = 'author';
    case User       = 'user';
    case Unverified = 'unverified';
    case Guest      = 'guest';
}
