<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Menu;
use App\Models\Page;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                        ->relationship('site', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                ]),

                Select::make('author_id')
                    ->label('Auteur')
                    ->relationship('author', 'name')   // koppelt aan users.name
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->default(fn() => auth()->id())   // default bij aanmaken
                    ->required(),

                Select::make('editor_id')
                    ->label('Laatste editor')
                    ->relationship('editor', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->default(fn(?Page $record) => $record?->editor_id ?? auth()->id())
                    ->helperText('Wie als laatste inhoud heeft aangepast.'),

                Section::make('Content')->columns(1)->schema([
                    TextInput::make('title')->label('Titel')->required()->maxLength(190),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(190)
                        ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $livewire) {
                            $state = $livewire->form->getRawState();
                            return $rule->where('site_id', $state['site_id'] ?? null);
                        }),
                    Textarea::make('excerpt')->label('Samenvatting')->rows(3)->nullable(),
                    Textarea::make('content')->label('Inhoud')->rows(10)->nullable(),
                ]),

                Section::make('Publicatie')->columns(1)->schema([
                    Toggle::make('is_published')->label('Gepubliceerd')->default(false),
                ]),

                Section::make('SEO')->columns(1)->collapsed()->schema([
                    KeyValue::make('seo')->label('SEO JSON')->nullable(),
                ]),
                Section::make('Menu overrides')
                    ->description('Optioneel: overschrijf site defaults voor deze pagina.')
                    ->columns(3)
                    ->schema([
                        Select::make('header_menu_id')
                            ->label('Header menu (override)')
                            ->searchable()->preload()->native(false)->nullable()
                            ->options(fn(Get $get) => ($siteId = $get('site_id') ?? $get('../site_id'))
                                ? Menu::where('site_id', $siteId)->orderBy('title')->pluck('title', 'id')
                                : []),
                        Select::make('footer_menu_id')
                            ->label('Footer menu (override)')
                            ->searchable()->preload()->native(false)->nullable()
                            ->options(fn(Get $get) => ($siteId = $get('site_id') ?? $get('../site_id'))
                                ? Menu::where('site_id', $siteId)->orderBy('title')->pluck('title', 'id')
                                : []),
                        Select::make('sidebar_menu_id')
                            ->label('Sidebar menu (override)')
                            ->searchable()->preload()->native(false)->nullable()
                            ->options(fn(Get $get) => ($siteId = $get('site_id') ?? $get('../site_id'))
                                ? Menu::where('site_id', $siteId)->orderBy('title')->pluck('title', 'id')
                                : []),
                    ]),

                Section::make('Extra menus')
                    ->description('Voeg extra menus toe (bv. extra sidebars of contextuele menus).')
                    ->schema([
                        Repeater::make('extraMenus')
                            ->relationship() // Page::extraMenus()
                            ->columns(2)
                            ->addActionLabel('Extra menu toevoegen')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->searchable()->preload()->native(false)
                                    ->options(fn(Get $get) => ($siteId = $get('site_id') ?? $get('../../site_id'))
                                        ? Menu::where('site_id', $siteId)->orderBy('title')->pluck('title', 'id')
                                        : []),
                                TextInput::make('slot')
                                    ->label('Slot (optioneel)')
                                    ->placeholder('bv. sidebar-secondary, account...')
                                    ->maxLength(50),
                            ]),
                    ])
            ]);


    }
}
