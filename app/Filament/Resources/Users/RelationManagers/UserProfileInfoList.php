<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Components\TranslationTabs;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class UserProfileInfoList
{
    public static function schema(): array
    {
        return [
            Section::make('Koppeling')->columns(1)->schema([
                TextEntry::make('user.name')->label('Naam'),
                TextEntry::make('user.email')->label('E-mail'),
            ]),

            Section::make('Profiel')->columns(1)->schema([
                TextEntry::make('username')->label('Gebruikersnaam'),
                TextEntry::make('slug')->label('Slug'),

                // ✅ Alleen translatable velden tonen per locale:
                ...TranslationTabs::infolist(
                    fields: ['tagline', 'bio'],
                    componentMap: ['bio' => 'html']  // bio als HTML tonen
                ),

                IconEntry::make('is_profile_active')->label('Profiel actief')->boolean(),
            ]),

            Section::make('Media')->columns(1)->schema([
                SpatieMediaLibraryImageEntry::make('avatar')
                    ->collection('avatar')
                    ->label('Avatar')
                    ->conversion('thumb')
                    ->hiddenLabel(),
                SpatieMediaLibraryImageEntry::make('photos')
                    ->collection('photos')
                    ->label('Foto’s')
                    ->limit(6)
                    ->hiddenLabel(),
            ]),

            Section::make('Meta')->columns(1)->schema([
                TextEntry::make('created_at')->label('Aangemaakt')->since(),
                TextEntry::make('updated_at')->label('Bijgewerkt')->since(),
            ]),
        ];
    }
}
