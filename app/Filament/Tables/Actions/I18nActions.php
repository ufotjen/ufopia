<?php

namespace App\Filament\Tables\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

final class I18nActions
{
    /**
     * Vult ALLE niet-fallback talen met fallback, zonder bestaande te overschrijven.
     */

    public static function copyFallback(): Action
    {
        return Action::make('copyFallback')
            ->label('Kopieer fallback → lege vertalingen')
            ->icon('heroicon-o-arrow-down-tray')
            ->requiresConfirmation()
            ->action(function ($record) {
                if (! $record) {
                    Notification::make()->title('Geen record')->danger()->send();
                    return;
                }

                $fallback = (string) config('app.fallback_locale', 'en');
                $locales  = (array) config('app.supported_locales', ['en','nl','fr','de']);
                $fields   = method_exists($record, 'getTranslatableAttributes')
                    ? $record->getTranslatableAttributes()
                    : (property_exists($record, 'translatable') ? (array) $record->translatable : []);

                $locked = method_exists($record, 'lockedLocales') ? (array) $record->lockedLocales() : [];
                $changed = 0;

                foreach ($fields as $field) {
                    $vals = (array) $record->getAttribute($field);
                    $src  = $vals[$fallback] ?? null;
                    if ($src === null || $src === '') continue;

                    foreach ($locales as $to) {
                        if ($to === $fallback) continue;
                        if (in_array($to, $locked, true)) continue;
                        if (!empty($vals[$to])) continue; // overschrijf niet

                        $vals[$to] = $src;
                        $changed++;
                    }

                    $record->setAttribute($field, $vals);
                }

                if ($changed) $record->saveQuietly();

                Notification::make()
                    ->title($changed ? "Fallback gekopieerd naar {$changed} lege veld(en)" : 'Niets te kopiëren')
                    ->success()
                    ->send();
            });
    }

    // Forceert vertaling via jouw SmartTranslator-router en overschrijft bestaande.
    public static function forceTranslate(): Action
    {
        return Action::make('forceTranslate')
            ->label('Forceer hervertaling')
            ->icon('heroicon-o-arrow-path')
            ->requiresConfirmation()
            ->action(function ($record) {
                if (! $record) {
                    Notification::make()->title('Geen record')->danger()->send();
                    return;
                }

                // locales = null ⇒ gebruik config('translation.locales')
                $filled = (int) $record->forceTranslate(locales: null, overwrite: true);

                Notification::make()
                    ->title($filled > 0 ? "Vertalingen ververst ({$filled})" : 'Niets te vertalen')
                    ->success()
                    ->send();
            });
    }
}
