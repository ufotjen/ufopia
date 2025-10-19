<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Filament\Components\TranslationTabs;
use App\Filament\Forms\Components\Reusable\I18nControls;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $cols = count(I18nControls::locales());

        return $schema->components([
            Section::make('Vertaalstatus')
                ->columns($cols)
                ->schema(static::statusBadges())->columnSpanFull(),
            Section::make('Vertaling')
                ->schema(
                    TranslationTabs::infolist(
                        fields: ['title', 'slug'],
                        componentMap: [],        // optioneel; matrix gebruikt vooral TextEntry
                        layout: 'matrix'         // ← DIT schakelt je nieuwe layout aan
                    )
                )->columnSpanFull(),

        ]);
    }

    /**
     * Maak voor elke locale een badge die toont of title/slug ingevuld zijn.
     * ✓ = beide aanwezig! = (deels) ontbrekend.
     */
    protected static function statusBadges(): array
    {
        $badges = [];

        foreach (I18nControls::locales() as $loc) {
            $label = strtoupper($loc)
                . ($loc === I18nControls::fallback() ? ' (default)' : '');

            $badges[] = TextEntry::make("status_$loc")
                ->label($label)
                ->badge()
                ->getStateUsing(function ($record) use ($loc) {
                    // Probeer alle vertalingen via Spatie
                    $titleVals = method_exists($record, 'getTranslations')
                        ? (array) $record->getTranslations('title')
                        : (array) data_get($record, 'title', []);

                    $slugVals = method_exists($record, 'getTranslations')
                        ? (array) $record->getTranslations('slug')
                        : (array) data_get($record, 'slug', []);

                    // Als het strings zijn (current-locale), maak er een array van
                    if (is_string($titleVals)) $titleVals = [$loc => $titleVals];
                    if (is_string($slugVals))  $slugVals  = [$loc => $slugVals];

                    $title = trim((string) ($titleVals[$loc] ?? ''));
                    $slug  = trim((string) ($slugVals[$loc]  ?? ''));

                    $missing = [];
                    if ($title === '') $missing[] = 'title';
                    if ($slug  === '') $missing[] = 'slug';

                    return empty($missing) ? '✓ ok' : '! ontbreekt: ' . implode(', ', $missing);
                })
                ->color(fn (string $state) => str_starts_with($state, '✓') ? 'success' : 'warning');
        }

        return $badges;
    }
}
