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
        return $schema
            ->components([
                // 🟢 Statusbar met per-taal badges (title + slug aanwezig?)
                Section::make('Vertaalstatus')
                    ->columns(count(I18nControls::locales()))
                    ->schema(static::statusBadges()),

                // 🌐 Tabs per taal met de vertaalde velden
                ...TranslationTabs::infolist(
                    fields: ['title', 'slug'], // zet op null om auto uit $translatable te nemen
                    componentMap: [
                        // voorbeeld: 'team_meta' => 'json', 'content' => 'html'
                    ]
                ),
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
            $label = strtoupper($loc) . ($loc === I18nControls::fallback() ? ' (default)' : '');

            $badges[] = TextEntry::make("status_$loc")
                ->label($label)
                ->badge()
                ->getStateUsing(function ($record) use ($loc) {
                    $title = (string) data_get($record, "title.$loc", '');
                    $slug  = (string) data_get($record, "slug.$loc", '');

                    $missing = [];
                    if ($title === '') $missing[] = 'title';
                    if ($slug === '')  $missing[] = 'slug';

                    if (empty($missing)) {
                        return '✓ ok';
                    }

                    return '!' . ' ontbreekt: ' . implode(', ', $missing);
                })
                ->color(function ($state) {
                    if (str_starts_with($state, '✓')) return 'success';
                    return 'warning';
                });
        }

        return $badges;
    }
}
