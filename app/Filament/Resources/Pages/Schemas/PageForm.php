<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Components\TranslationTabs;
use App\Filament\Forms\Components\Reusable\I18nControls;
use App\Models\Menu;
use App\Models\Page;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Koppeling')->columns(1)->schema([
                    Select::make('site_id')
                        ->relationship('site', 'name->' . app()->getLocale())
                        ->required()
                        ->searchable()
                        ->preload(),
                ]),

                Select::make('author_id')
                    ->label('Auteur')
                    ->relationship('author', 'name')
                    ->searchable()->preload()->native(false)
                    ->default(fn () => auth()->id())
                    ->required(),

                Select::make('editor_id')
                    ->label('Laatste editor')
                    ->relationship('editor', 'name')
                    ->searchable()->preload()->native(false)
                    ->default(fn (?Page $record) => $record?->editor_id ?? auth()->id())
                    ->helperText('Wie als laatste inhoud heeft aangepast.'),

                ...I18nControls::make(),

                TranslationTabs::form(
                    schemaForLocale: fn (string $loc, bool $isFallback) => [
                        KeyValue::make("team_meta.$loc")
                            ->label('Team metadata')
                            ->keyLabel('key')->valueLabel('value'),
                    ],
                    componentMap: [
                        'content' => 'rich',
                        'excerpt' => 'textarea',
                        'seo'     => 'keyvalue',
                        // 'team_meta' => 'keyvalue', // kan ook via componentMap i.p.v. closure
                    ]
                ),

                Section::make('Publicatie')->columns(1)->schema([
                    Toggle::make('is_published')
                        ->label('Gepubliceerd')
                        ->default(false),
                ]),

                Section::make('Menu overrides')
                    ->description('Optioneel: overschrijf site defaults voor deze pagina.')
                    ->columns(3)
                    ->schema([
                        Select::make('header_menu_id')
                            ->label('Header menu (override)')
                            ->searchable()->preload()->native(false)->nullable()
                            ->options(fn (Get $get) => ($siteId = $get('site_id') ?? $get('../site_id'))
                                ? Menu::query()
                                    ->where('site_id', $siteId)
                                    ->get()
                                    ->mapWithKeys(fn ($m) => [$m->id => $m->getTranslation('title', app()->getLocale())])
                                    ->all()
                                : []),
                        Select::make('footer_menu_id')
                            ->label('Footer menu (override)')
                            ->searchable()->preload()->native(false)->nullable()
                            ->options(fn (Get $get) => ($siteId = $get('site_id') ?? $get('../site_id'))
                                ? Menu::query()
                                    ->where('site_id', $siteId)
                                    ->get()
                                    ->mapWithKeys(fn ($m) => [$m->id => $m->getTranslation('title', app()->getLocale())])
                                    ->all()
                                : []),
                        Select::make('sidebar_menu_id')
                            ->label('Sidebar menu (override)')
                            ->searchable()->preload()->native(false)->nullable()
                            ->options(fn (Get $get) => ($siteId = $get('site_id') ?? $get('../site_id'))
                                ? Menu::query()
                                    ->where('site_id', $siteId)
                                    ->get()
                                    ->mapWithKeys(fn ($m) => [$m->id => $m->getTranslation('title', app()->getLocale())])
                                    ->all()
                                : []),
                    ]),

                Section::make('Extra menus')
                    ->description('Voeg extra menus toe (bv. extra sidebars of contextuele menus).')
                    ->schema([
                        Repeater::make('extraMenus')
                            ->relationship()
                            ->columns()
                            ->addActionLabel('Extra menu toevoegen')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->searchable()->preload()->native(false)
                                    ->options(fn (Get $get) => ($siteId = $get('site_id') ?? $get('../../site_id'))
                                        ? Menu::query()
                                            ->where('site_id', $siteId)
                                            ->get()
                                            ->mapWithKeys(fn ($m) => [$m->id => $m->getTranslation('title', app()->getLocale())])
                                            ->all()
                                        : []),
                                TextInput::make('slot')
                                    ->label('Slot (optioneel)')
                                    ->placeholder('bv. sidebar-secondary, account...')
                                    ->maxLength(50),
                            ]),
                    ]),
            ]);
    }
}
