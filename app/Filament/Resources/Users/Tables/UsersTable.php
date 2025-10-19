<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('E-mail gekopieerd')
                    ->copyMessageDuration(1500)
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('email_verified_at')
                    ->label('E-mail geverifieerd')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('Niet bevestigd')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('two_factor_confirmed_at')
                    ->label('2FA bevestigd')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('Niet ingesteld')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('roles.name')
                    ->label('Rollen')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),

                IconColumn::make('is_active')   // ← bestond wel in je model
                ->label('Actief')
                    ->boolean(),

                IconColumn::make('soft_blocked')
                    ->label('Soft blocked')
                    ->boolean(),

                TextColumn::make('suspended_until')
                    ->label('Schorsing')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->multiple(), // vaak nuttig
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
