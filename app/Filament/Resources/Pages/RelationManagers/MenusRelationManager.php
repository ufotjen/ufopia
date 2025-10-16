<?php

namespace App\Filament\Resources\Pages\RelationManagers;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
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
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('title')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Locatie')
                    ->badge()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true)])
            ->headerActions([
                AttachAction::make()
                    ->schema([
                        // 'recordId' is de standaard attach-picker
                        Select::make('recordId')
                            ->label('Menu')
                            ->relationship('extraMenus', 'title')
                            ->required(),
                        Select::make('location')
                            ->label('Locatie')
                            ->options([
                                'header'  => 'Header',
                                'footer'  => 'Footer',
                                'sidebar' => 'Sidebar',
                                'custom'  => 'Custom',
                            ])
                            ->required(),
                    ])
                    ->using(function ($page, array $data) {
                        // per locatie maximaal één menu (optioneel beleid)
                        $page->extraMenus()->wherePivot('location', $data['location'])->detach();
                        $page->extraMenus()->attach($data['recordId'], ['location' => $data['location']]);
                    }),
            ])
            ->recordActions([
                    EditAction::make()
                        ->schema([
                            Select::make('location')
                                ->label('Locatie')
                                ->options([
                                    'header'  => 'Header',
                                    'footer'  => 'Footer',
                                    'sidebar' => 'Sidebar',
                                    'custom'  => 'Custom',
                                ])->required(),
                        ]),
                    DetachAction::make()->label('Ontkoppelen'),
                ViewAction::make(),


            ])
            ->groupedBulkActions([
                DetachBulkAction::make()
                    ->label('Ontkoppelen (bulk)'),
            ]);
    }
}
