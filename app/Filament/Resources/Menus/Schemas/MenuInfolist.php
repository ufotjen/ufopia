<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Overzicht')->columns(2)->schema([
                    TextEntry::make('site.name')->label('Site'),
                    TextEntry::make('title')->label('Naam'),
                    TextEntry::make('key')->label('Locatie')->badge(),
                    TextEntry::make('description')->label('Omschrijving')->placeholder('—')->columnSpanFull(),

                ])]);
    }
}
