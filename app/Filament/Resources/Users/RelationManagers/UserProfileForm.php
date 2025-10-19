<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Components\TranslationTabs;
use App\Filament\Forms\Components\Reusable\I18nControls;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\{Get, Set};        // ✅ 4.1 Set/Get
use Illuminate\Support\Str;

class UserProfileForm
{
    public static function schema(): array
    {
        return [
            // ⚠️ In een RelationManager meestal verbergen:
            Section::make('Koppeling')
                ->columns(1)
                ->hidden()                                      // ← verberg in RM-context
                ->schema([
                    Select::make('user_id')
                        ->label('Gebruiker')
                        ->relationship('user', 'email')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),

            Section::make('Profiel')
                ->columns(1)
                ->schema([
                    TextInput::make('username')
                        ->label('Gebruikersnaam')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(190)
                        ->live(debounce: 400)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            if ($get('sync_slug')) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    Toggle::make('sync_slug')
                        ->label('Koppel slug aan gebruikersnaam')
                        ->default(true)
                        ->inline(false)
                        ->dehydrated(false),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->rules(['alpha_dash'])
                        ->maxLength(190)
                        ->helperText('Gebruikt in de URL. Zet “koppel slug” uit om handmatig aan te passen.'),

                    ...I18nControls::make(),

                    // 🔤 Alleen translatable velden in tabs:
                    TranslationTabs::form(
                        fields: ['tagline', 'bio'],
                        schemaForLocale: function (string $loc, bool $isFallback) {
                            return [
                                TextInput::make("tagline.$loc")
                                    ->label('Slagzin')
                                    ->maxLength(190),

                                // Je kan RichEditor ook via componentMap forceren;
                                // hier doen we ’m gewoon expliciet.
                                RichEditor::make("bio.$loc")
                                    ->label('Bio')
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'bold','italic','underline','strike',
                                        'h2','h3','blockquote','codeBlock',
                                        'bulletList','orderedList',
                                        'link','horizontalRule','undo','redo','table',
                                    ])
                                    ->fileAttachmentsDirectory('profiles/bio'),
                            ];
                        },
                        componentMap: [
                            'bio' => 'rich',                     // optioneel, maar consistent
                        ]
                    ),

                    Toggle::make('is_profile_active')
                        ->label('Profiel actief')
                        ->default(true),
                ]),

            Section::make('Media')
                ->columns(1)
                ->schema([
                    SpatieMediaLibraryFileUpload::make('avatar')
                        ->label('Avatar')
                        ->collection('avatar')
                        ->image()
                        ->imageEditor()
                        ->downloadable()
                        ->openable()
                        ->maxFiles(1),

                    SpatieMediaLibraryFileUpload::make('photos')
                        ->label('Foto’s')
                        ->collection('photos')
                        ->image()
                        ->imageEditor()
                        ->multiple()
                        ->reorderable()
                        ->downloadable()
                        ->openable(),
                ]),

            Section::make('Social & voorkeuren')
                ->columns(1)
                ->schema([
                    KeyValue::make('social_links')
                        ->label('Social links')
                        ->keyLabel('platform')->valueLabel('url')
                        ->keyPlaceholder('platform')->valuePlaceholder('https://…')
                        ->addActionLabel('Toevoegen')->deleteActionLabel('Verwijderen')
                        ->reorderable(),

                    KeyValue::make('preferences')
                        ->label('Voorkeuren (JSON)')
                        ->keyLabel('voorkeur')->valueLabel('waarde')
                        ->keyPlaceholder('sleutel')->valuePlaceholder('waarde')
                        ->addActionLabel('Toevoegen')->deleteActionLabel('Verwijderen')
                        ->reorderable(),
                ]),
        ];
    }
}
