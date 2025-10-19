<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Components\TranslationTabs;
use App\Filament\Forms\Components\Reusable\I18nControls;
use App\Models\UserProfile;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;                       // ✅ Schema API container
use Filament\Schemas\Components\Utilities\{Get, Set};          // ✅ live updates via Schema API
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class UserProfileRelationManager extends RelationManager
{
    protected static string $relationship = 'profile';
    protected static ?string $title = 'Profiel';

    public static function getModelLabel(): string { return 'Profile'; }
    public static function getPluralModelLabel(): string { return 'Profile'; }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // ── Profiel (niet-vertaalbare basisvelden) ───────────────────────────────
            Section::make('Profiel')->columns(1)->schema([
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
                    ->helperText('Gebruikt in de URL. Zet “koppel slug” uit om handmatig aan te passen.')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(190),
            ]),



            // ── Vertalingen: ENKEL tagline + bio (géén slug hier) ──────────────────
            Section::make('Vertaalbare velden')->columns(1)->schema([
                ...I18nControls::make(),
                TranslationTabs::form(
                    fields: ['tagline', 'bio'],
                    schemaForLocale: null,
                    componentMap: [
                        'bio' => 'textarea',   // bio → textarea, tagline → textinput
                    ],
                ),
            ]),

            // ── Media ────────────────────────────────────────────────────────────────
            Section::make('Media')->columns(1)->schema([
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

            // ── Socials & voorkeuren ────────────────────────────────────────────────
            Section::make('Social & voorkeuren')->columns(1)->schema([
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
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        $loc = app()->getLocale();

        return $table
            ->recordTitleAttribute('username')
            ->columns([
                TextColumn::make('username')->label('Gebruikersnaam')->searchable(),
                TextColumn::make("slug")->label('Slug')->toggleable(),
                IconColumn::make('is_profile_active')->label('Actief')->boolean(),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create profile')
                    ->visible(fn ($livewire) => $livewire->getOwnerRecord()->profile()->doesntExist()),

            ])
            ->recordActions([
                Action::make('forceTranslate')
                    ->label('Forceer hervertaling')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (UserProfile $record) {
                        $count = $record->forceTranslate();

                        if ($count > 0) {
                            Notification::make()
                                ->title("Vertaling ingevuld voor {$count} velden")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Niets te vertalen')
                                ->body('Zorg voor bronwaarde in de fallback-taal en controleer config(translations).')
                                ->warning()
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->label('Remove profile')
                    ->visible(fn () => auth()->user()?->can('users.delete_soft') ?? false),
            ]);
    }
}
