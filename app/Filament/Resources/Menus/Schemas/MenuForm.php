<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Enums\MenuLocation;
use App\Filament\Components\TranslationTabs;
use App\Filament\Forms\Components\Reusable\I18nControls;
use App\Models\Menu;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            // 👇 GEEN ->record(), GEEN loadMissing(), GEEN closures die andere Schema's oproepen
            ->components([
                Section::make('Basis')->schema([
                    Select::make('site_id')
                        ->label('Site')
                        ->relationship('site', 'name')
                        ->searchable()->preload()->native(false)->required(),

                    Select::make('key')
                        ->label('Locatie')
                        ->options([
                            MenuLocation::HEADER->value => 'Header',
                            MenuLocation::FOOTER->value => 'Footer',
                            MenuLocation::SIDEBAR->value => 'Sidebar',
                            MenuLocation::OTHER->value => 'Other (nood)',
                        ])
                        ->required()
                        ->native(false)
                        ->rule(fn (Get $get, ? Menu $record) =>
                        Rule::unique('menus', 'key')
                            ->where(fn ($q) => $q->where('site_id', (int) ($get('site_id') ?? $record?->site_id)))
                            ->ignore($record?->getKey())
                        )
                        ->helperText('Per site is elke locatie uniek. "Other" enkel voor uitzonderingen.'),

                    Toggle::make('sync_slug')
                        ->label('Koppel slug aan titel')
                        ->default(true)
                        ->inline(false)
                        ->dehydrated(false),
                    ...I18nControls::make(),

                    TranslationTabs::form(
                        fields: [], // of null = auto
                        schemaForLocale: function (string $loc, bool $isFallback) {
                            return [
                                TextInput::make("title.$loc")
                                    ->label('Titel')
                                    ->required($isFallback)
                                    ->live(debounce: 400)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) use ($loc) {
                                        if ($get('sync_slug')) {
                                            $set("slug.$loc", Str::slug((string)$state));
                                        }
                                    }),

                                TextInput::make("slug.$loc")
                                    ->label('Slug')
                                    ->rules(['alpha_dash'])
                                    ->unique(
                                        table: Menu::class,                     // ← Menu i.p.v. Page
                                        column: "slug->$loc",                  // JSON path per locale
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn(Unique $rule, Get $get) => $rule->where('site_id', (int)$get('site_id'))
                                    ),
                            ];
                        }),
                ])
            ]);
    }
}
