<?php

namespace App\Filament\Resources\Sites\Schemas;

use App\Models\Menu;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('owner_id')
                    ->label(__('Eigenaar'))
                    ->relationship('owner', 'name')   // 👈 slaat owner_id op, toont User.name
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                TextInput::make('name')
                    ->label(__('Naam'))
                    ->required()
                    ->live(debounce: 400) // update ‘on type’
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        if ($get('sync_slug')) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                Toggle::make('sync_slug')
                    ->label('Koppel slug aan naam')
                    ->default(true)
                    ->inline(false)
                    ->dehydrated(false),
                TextInput::make('slug')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('primary_domain'),
                TextInput::make('extra_domains'),
                TextInput::make('default_locale')
                    ->required()
                    ->default('nl'),
                TextInput::make('locales'),
                TextInput::make('theme_key')
                    ->required()
                    ->default('tailwind-daisyui'),
                TextInput::make('theme_overrides'),
                TextInput::make('timezone'),
                TextInput::make('contact_email')
                    ->email(),
                TextInput::make('feature_flags'),
                TextInput::make('options'),
                TextInput::make('team_meta'),
                Select::make('header_menu_id')
                    ->label('Default Header Menu')
                    ->searchable()
                    ->preload()
                    ->options(fn(Get $get) => Menu::where('site_id', $get('id') ?? $get('../id'))->pluck('title', 'id'))
                    ->native(false)
                    ->nullable(),

                Select::make('footer_menu_id')
                    ->label('Default Footer Menu')
                    ->searchable()
                    ->preload()
                    ->options(fn(Get $get) => Menu::where('site_id', $get('id') ?? $get('../id'))->pluck('title', 'id'))
                    ->native(false)
                    ->nullable(),

                Select::make('sidebar_menu_id')
                    ->label('Default Sidebar Menu')
                    ->searchable()
                    ->preload()
                    ->options(fn(Get $get) => Menu::where('site_id', $get('id') ?? $get('../id'))->pluck('title', 'id'))
                    ->native(false)
                    ->nullable()
            ]);
    }
}
