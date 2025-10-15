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
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable()
                    ->wrap() // lange namen breken netjes
                    ->tooltip(fn($state) => $state) // hover toont volledige naam
                    // ->description('Volledige weergavenaam', position: 'below') // optioneel
                    ->toggleable(), // kolom verberg-/toonbaar in table settings


                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable()               // klik-om-te-kopiëren
                    ->copyMessage('E-mail gekopieerd')
                    ->copyMessageDuration(1500)
                    ->placeholder('—')         // als leeg
                    ->icon('heroicon-m-envelope') // klein icoon vooraan (optioneel)
                    ->wrap(),

                TextColumn::make('email_verified_at')
                    ->label('E-mail geverifieerd')
                    ->dateTime('Y-MM-dd HH:mm')
                    ->placeholder('Niet bevestigd')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('two_factor_confirmed_at')
                    ->label('2FA bevestigd')
                    ->dateTime('Y-MM-dd HH:mm')
                    ->placeholder('Niet ingesteld')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('roles.name')
                    ->label('Rollen')
                    ->badge()
                    ->separator(', '),

                IconColumn::make('is_inactive')
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')->relationship('roles', 'name')->label('Rol'),
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
