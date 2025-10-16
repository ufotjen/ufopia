<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return  $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('site.name')->label('Site')->sortable(),
                TextColumn::make('title')->label('Naam')->searchable()->sortable(),
                TextColumn::make('key')->label('Locatie')->badge()->sortable(),
                TextColumn::make('updated_at')->since()->label('Bijgewerkt')->sortable(),
            ])
            ->filters([
                SelectFilter::make('site_id')->relationship('site', 'name')->label('Site'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
