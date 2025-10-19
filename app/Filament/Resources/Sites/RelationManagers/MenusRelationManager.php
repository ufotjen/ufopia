<?php

namespace App\Filament\Resources\Sites\RelationManagers;

use App\Filament\Components\TranslationTabs;
use App\Filament\Resources\Menus\MenuResource;
use App\Models\Menu;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class MenusRelationManager extends RelationManager
{
    protected static string $relationship = 'menus';

    protected static ?string $relatedResource = MenuResource::class;

    public static function getRecordTitle(?Menu $record): ?string
    {
        return $record?->getTranslation('title', app()->getLocale());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Sleutel')
                    ->required()
                    ->maxLength(190)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule) => $rule->where('site_id', $this->getOwnerRecord()->id)
                    ),

                TranslationTabs::form(
                    fields: ['title', 'slug'],
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
                                    table: Menu::class,
                                    column: "slug->$loc",
                                    ignoreRecord: true,
                                    modifyRuleUsing: fn(Unique $rule) => $rule->where('site_id', $this->getOwnerRecord()->id)
                                ),
                        ];
                    }
                ),

                Toggle::make('sync_slug')
                    ->label('Koppel slug aan titel')
                    ->default(true)
                    ->inline(false)
                    ->dehydrated(false),
            ])
            ->columns(1); // layout;
    }

    public function table(Table $table): Table
    {
        $loc = app()->getLocale();

        return $table
            ->query(fn(Builder $q) => $q->with(['site:id,name'])
            )
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make("title.$loc")->label('Naam')->searchable()->sortable(),
                TextColumn::make("slug.$loc")->label('Slug')->searchable()->sortable(),
                TextColumn::make('key')->label('Key')->badge()->sortable(),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
