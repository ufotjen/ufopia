<?php

namespace App\Enums;

enum MenuLocation : String
{
    case HEADER = 'Header';
    case FOOTER = 'Footer';
    case SIDEBAR = 'Sidebar';
    case OTHER = 'Other';
}
