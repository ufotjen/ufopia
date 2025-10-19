<?php

namespace App\Filament\Forms\Components\Reusable;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

final class IconPicker
{
    /**
     * @param 'columns'|'json' $storage
     *   columns → gebruikt 'icon_mode' & 'icon_class'
     *   json    → gebruikt 'i18n_overrides.icon.mode' & 'i18n_overrides.icon.class'
     *
     * @return array<\Filament\Forms\Components\Component>
     */
    public static function make(string $storage = 'columns'): array
    {
        $isJson = $storage === 'json';

        $modeField  = $isJson ? 'i18n_overrides.icon.mode'  : 'icon_mode';
        $classField = $isJson ? 'i18n_overrides.icon.class' : 'icon_class';

        return [
            Section::make('Icon')
                ->schema([
                    Radio::make($modeField)
                        ->label('Icon bron')
                        ->options([
                            'none'  => 'Geen',
                            'media' => 'Upload (afbeelding / SVG)',
                            'class' => 'Icon class (thema)',
                        ])
                        ->inline()
                        ->live()
                        ->default('none'),

                    // Media upload: let op → geen DB-kolom; Spatie Media Library koppelt aan 'icon' collectie.
                    SpatieMediaLibraryFileUpload::make('icon')
                        ->label('Icon upload')
                        ->collection('icon')
                        ->image()
                        ->acceptedFileTypes(['image/png', 'image/webp', 'image/svg+xml'])
                        ->visible(fn ($get) => ($get($modeField) ?? 'none') === 'media'),

                    TextInput::make($classField)
                        ->label('Icon class')
                        ->placeholder('bv. lucide-home / heroicon-m-home / ph-house')
                        ->helperText('Kies een class passend bij het geselecteerde thema.')
                        ->visible(fn ($get) => ($get($modeField) ?? 'none') === 'class'),
                ])
                ->columns(1),
        ];
    }
}
