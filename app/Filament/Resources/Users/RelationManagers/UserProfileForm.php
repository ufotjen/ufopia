<?php


namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

class UserProfileForm
{
    public static function schema(): array
    {
        return [
            // In een RelationManager wil je deze vaak NIET tonen. Zie stap 2.
            Section::make('Koppeling')
                ->columns(1)
                ->extraAttributes(['class' => 'space-y-4'])
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
                ->extraAttributes(['class' => 'space-y-4'])
                ->schema([
                    TextInput::make('username')
                        ->label('Gebruikersnaam')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(190)
                        ->live(debounce: 400)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($get('sync_slug')) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(190)
                        ->helperText('Gebruikt in de URL. Zet “koppel slug” uit om handmatig aan te passen.'),

                    Toggle::make('sync_slug')
                        ->label('Koppel slug aan gebruikersnaam')
                        ->default(true)
                        ->inline(false)
                        ->dehydrated(false),

                    TextInput::make('tagline')
                        ->label('Slagzin')
                        ->maxLength(255),

                    RichEditor::make('bio')
                        ->label('Bio')
                        ->toolbarButtons([
                            'bold','italic','underline','strike',
                            'h2','h3','blockquote','codeBlock',
                            'bulletList','orderedList',
                            'link','horizontalRule','undo','redo','table',
                        ])
                        ->fileAttachmentsDirectory('profiles/bio'),

                    Toggle::make('is_profile_active')
                        ->label('Profiel actief')
                        ->default(true),
                ]),

            Section::make('Media')
                ->columns(1)
                ->extraAttributes(['class' => 'space-y-4'])
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
                ->extraAttributes(['class' => 'space-y-4'])
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
