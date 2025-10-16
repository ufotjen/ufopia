<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Enums\MenuLocation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Rules\Enum as EnumRule;

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
                            MenuLocation::HEADER->value  => 'Header',
                            MenuLocation::FOOTER->value  => 'Footer',
                            MenuLocation::SIDEBAR->value => 'Sidebar',
                            MenuLocation::OTHER->value   => 'Other (nood)',
                        ])
                        ->required()
                        ->native(false)
                        ->rule(new EnumRule(MenuLocation::class))
                        ->rule(fn (Get $get) => (new Unique('menus','key'))
                            ->where('site_id', (int) $get('site_id'))
                            ->ignore(request()->route('record')))
                        ->helperText('Per site is elke locatie uniek. "Other" enkel voor uitzonderingen.'),

                    TextInput::make('title')
                        ->label('Titel')
                        ->required()
                        ->maxLength(190)
                        ->live(debounce: 400)
                        ->afterStateUpdated(function ($set, Get $get, ?string $state) {
                            if ($get('sync_slug')) $set('slug', Str::slug((string) $state));
                        }),

                    Toggle::make('sync_slug')
                        ->label('Koppel slug aan titel')
                        ->default(true)
                        ->inline(false)
                        ->dehydrated(false),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->rules(['alpha_dash'])
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule, Get $get) =>
                            $rule->where('site_id', (int) $get('site_id'))
                        )
                        ->live(debounce: 400),

                    Toggle::make('is_active')->label('Actief')->default(true),
                ])
            ]);
    }
}
