<?php

namespace App\Filament\Resources\Pages\RelationManagers;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusRelationManager extends RelationManager
{
    protected static string $relationship = 'menus';

    protected static ?string $relatedResource = MenuResource::class;

    public function table(Table $table): Table
    {
        $loc = app()->getLocale();

        return $table
            ->recordTitleAttribute("title->$loc")
            ->columns([
                TextColumn::make("title.$loc")
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make("slug.$loc")
                    ->label('Slug')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),

                // Pivot-veld (ik ga uit van 'slot' i.p.v. 'location' zoals je Repeater)
                TextColumn::make('pivot.slot')
                    ->label('Slot')
                    ->badge()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // Attach naar dezelfde site als de Page (owner record)
                AttachAction::make()
                    ->label('Menu koppelen')
                    ->recordSelectOptionsQuery(fn ($query) => $query->where('site_id', $this->getOwnerRecord()->site_id))
                    ->preloadRecordSelect()
                    ->schema([
                        // pivot veld (slot)
                        TextInput::make('slot')
                            ->label('Slot')
                            ->maxLength(50)
                            ->required(),
                    ]),
            ])
            ->recordActions([
                // Pivot bewerken (alleen 'slot' hier)
                EditAction::make()
                    ->label('Slot wijzigen')
                    ->modalHeading('Slot wijzigen')
                    ->schema([
                        TextInput::make('slot')
                            ->label('Slot')
                            ->maxLength(50)
                            ->required(),
                    ]),
                DetachAction::make()->label('Ontkoppelen'),
                ViewAction::make(),
            ])
            ->groupedBulkActions([
                DetachBulkAction::make()->label('Ontkoppelen (bulk)'),
            ]);
    }
}
