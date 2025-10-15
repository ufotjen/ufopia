<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Naam'),
                        TextEntry::make('email')->label('E-mail'),
                        TextEntry::make('roles.name')->label('Rollen')->badge()->separator(', '),
                        TextEntry::make('email_verified_at')
                            ->label('E-mail geverifieerd')
                            ->dateTime('Y-MM-dd HH:mm')
                            ->placeholder('Niet bevestigd'),
                        TextEntry::make('two_factor_confirmed_at')
                            ->label('2FA bevestigd')
                            ->dateTime('Y-MM-dd HH:mm')
                            ->placeholder('Niet ingesteld'),
                    ]),
                Section::make('Status')
                    ->columns(3)
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Actief')
                            ->boolean(),
                        IconEntry::make('soft_blocked')
                            ->label('Soft blocked')
                            ->boolean(),
                        TextEntry::make('suspended_until')->label('Geschorst tot')->dateTime('Y-MM-dd HH:mm')->placeholder('—'),
                    ]),
                Section::make('Meta')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label('Aangemaakt')->since(),
                        TextEntry::make('updated_at')->label('Bijgewerkt')->since(),
                    ]),
            ]);
    }
}
