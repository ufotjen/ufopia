<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Tables\Actions\I18nActions;
use Filament\Actions\CreateAction;
use Filament\Infolists\Components\IconEntry;    // ✅ juiste Section voor Infolists
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->schema([
                    TextEntry::make('name')->label('Naam'),
                    TextEntry::make('email')->label('E-mail'),

                    TextEntry::make('roles.name')
                        ->label('Rollen')
                        ->badge()
                        ->separator(', '),

                    TextEntry::make('email_verified_at')
                        ->label('E-mail geverifieerd')
                        ->dateTime('Y-m-d H:i')
                        ->placeholder('Niet bevestigd'),

                    TextEntry::make('two_factor_confirmed_at')
                        ->label('2FA bevestigd')
                        ->dateTime('Y-m-d H:i')
                        ->placeholder('Niet ingesteld'),
                ]),

            Section::make('Status')
                ->columns(3)
                ->schema([
                    IconEntry::make('is_active')->label('Actief')->boolean(),
                    IconEntry::make('soft_blocked')->label('Soft blocked')->boolean(),
                    TextEntry::make('suspended_until')
                        ->label('Geschorst tot')
                        ->dateTime('Y-m-d H:i')
                        ->placeholder('—'),
                ]),

            Section::make('Meta')
                ->schema([
                    TextEntry::make('created_at')->label('Aangemaakt')->since(),
                    TextEntry::make('updated_at')->label('Bijgewerkt')->since(),
                ])
        ]);
    }
}
