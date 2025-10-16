<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Models\Site;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SiteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->record(function ($record) {
                if (! $record) {
                    return $record; // belangrijk: niets doen als er (nog) geen record is
                }

                $record->loadMissing(['headerMenu', 'footerMenu', 'sidebarMenu'])
                    ->loadCount(['menus', 'pages']);

                return $record;
            })
            ->components([
                TextEntry::make('owner.name')->label('Eigenaar'),
                TextEntry::make('name')->label('Naam'),
                TextEntry::make('slug'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('primary_domain')
                    ->placeholder('-'),
                TextEntry::make('default_locale'),
                TextEntry::make('theme_key'),
                TextEntry::make('timezone')
                    ->placeholder('-'),
                TextEntry::make('contact_email')
                    ->placeholder('-'),
                Section::make('Site')->columns(2)->schema([
                    TextEntry::make('name')->label('Naam'),
                    TextEntry::make('slug')->label('Slug'),
                    TextEntry::make('primary_domain')->label('Domein')->columnSpanFull()->placeholder('—'),
                ]),
                Section::make('Default menus')->columns(3)->schema([
                    TextEntry::make('headerMenu.title')->label('Header')->badge()->placeholder('—'),
                    TextEntry::make('footerMenu.title')->label('Footer')->badge()->placeholder('—'),
                    TextEntry::make('sidebarMenu.title')->label('Sidebar')->badge()->placeholder('—'),
                ]),
                Section::make('Statistiek')->columns(2)->schema([
                    TextEntry::make('menus_count')->label('Menus')->getStateUsing(fn ($r) => $r->menus()->count()),
                    TextEntry::make('pages_count')->label('Pages')->getStateUsing(fn ($r) => $r->pages()->count()),
                ]),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
