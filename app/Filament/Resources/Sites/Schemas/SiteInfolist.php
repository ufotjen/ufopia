<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Filament\Components\TranslationTabs;
use Filament\Infolists\Components\TextEntry;

use Filament\Schemas\Schema;

class SiteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $loc = app()->getLocale();

        return $schema
            ->record(function ($record) {
                if (! $record) return $record;

                // enkel light eager-load die niet recursief is
                $record->loadMissing(['headerMenu', 'footerMenu', 'sidebarMenu']);

                return $record;
            })
            ->components([
                // Header
                TextEntry::make('id')->label('ID'),
                TextEntry::make('owner.name')->label('Eigenaar'),
                TextEntry::make("name}")->label('Naam'),

                // Vertaalde velden – spread array!
                ...TranslationTabs::infolist(
                    fields: ['slug', 'team_meta'],
                    componentMap: ['team_meta' => 'json'],
                ),

                // Theme
                TextEntry::make('theme_key')->label('Theme'),

                // Domein
                TextEntry::make('primary_domain')->label('Primair domein')->placeholder('—'),

                // Default menus (vertalingen per huidige locale)
                TextEntry::make("headerMenu.title->{$loc}")->label('Header')->badge()->placeholder('—'),
                TextEntry::make("footerMenu.title->{$loc}")->label('Footer')->badge()->placeholder('—'),
                TextEntry::make("sidebarMenu.title->{$loc}")->label('Sidebar')->badge()->placeholder('—'),

                // Counts rechtstreeks uit withCount
                TextEntry::make('menus_count')->label('Menus'),
                TextEntry::make('pages_count')->label('Pages'),

                // Timestamps
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}
