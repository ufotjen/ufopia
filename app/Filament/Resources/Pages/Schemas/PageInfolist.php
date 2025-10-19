<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Components\TranslationTabs;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...TranslationTabs::infolist(
                    componentMap: [
                        'content'   => 'html',
                        'seo'       => 'json',
                        'team_meta' => 'json',
                    ]
                ),

                Section::make('Overzicht')->columns()->schema([
                    TextEntry::make('site.name')->label('Site'),
                    TextEntry::make('author.name')->label('Auteur'),
                    TextEntry::make('editor.name')->label('Laatst bewerkt door'),
                    IconEntry::make('is_published')->label('Gepubliceerd')->boolean(),
                ]),

                Section::make('Menus (effective)')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('effective_header')
                            ->label('Header')->badge()
                            ->getStateUsing(fn ($r) => $r->effectiveHeaderMenu()?->title ?? '—'),
                        TextEntry::make('effective_footer')
                            ->label('Footer')->badge()
                            ->getStateUsing(fn ($r) => $r->effectiveFooterMenu()?->title ?? '—'),
                        TextEntry::make('effective_sidebar')
                            ->label('Sidebar')->badge()
                            ->getStateUsing(fn ($r) => $r->effectiveSidebarMenu()?->title ?? '—'),
                    ]),

                Section::make('Overrides (pagina-niveau)')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('headerMenu.title')->label('Header override')->placeholder('—')->badge(),
                        TextEntry::make('footerMenu.title')->label('Footer override')->placeholder('—')->badge(),
                        TextEntry::make('sidebarMenu.title')->label('Sidebar override')->placeholder('—')->badge(),
                    ]),

                Section::make('Extra menus')->schema([
                    RepeatableEntry::make('extraMenus')
                        ->label('Extra\'s')
                        ->schema([
                            TextEntry::make('menu.title')->label('Menu')->badge(),
                            TextEntry::make('slot')->label('Slot')->placeholder('—'),
                        ])
                        ->columns(),
                ]),

                Section::make('Meta')->columns()->schema([
                    TextEntry::make('created_at')->since(),
                    TextEntry::make('updated_at')->since(),
                ]),
            ]);
    }
}
