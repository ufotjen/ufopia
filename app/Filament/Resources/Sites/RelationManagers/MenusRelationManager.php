<?php

namespace App\Filament\Resources\Sites\RelationManagers;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusRelationManager extends RelationManager
{
    protected static string $relationship = 'menus';

    protected static ?string $relatedResource = MenuResource::class;
    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->label('Naam')->required()->maxLength(190),
            TextInput::make('key')->label('Sleutel')->required()->maxLength(190)
                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                    return $rule->where('site_id', $this->getOwnerRecord()->id);
                }),
            TextInput::make('description')->label('Omschrijving')->maxLength(255)->nullable(),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Naam')->searchable()->sortable(),
                TextColumn::make('key')->label('Key')->sortable(),
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
