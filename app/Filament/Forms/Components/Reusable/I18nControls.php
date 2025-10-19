<?php

namespace App\Filament\Forms\Components\Reusable;

use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs\Tab;

final class I18nControls
{
    public static function locales(): array
    {
        return (array) config('app.supported_locales', ['en','nl','fr','de']);
    }

    /** Fallback locale (centraal) */
    public static function fallback(): string
    {
        return (string) config('app.fallback_locale', 'en');
    }

    /**
     * Opties voor dropdowns/checkboxes. Markeer optioneel de fallback.
     * Voorbeeld: ['nl' => 'NL (default)', 'en' => 'EN', ...]
     */
    public static function options(bool $markFallback = false): array
    {
        $fallback = self::fallback();

        return collect(self::locales())
            ->mapWithKeys(fn (string $l) => [
                $l => strtoupper($l) . ($markFallback && $l === $fallback ? ' (default)' : ''),
            ])
            ->all();
    }

    /** Handig als je snel wil checken of een locale de fallback is */
    public static function isFallback(string $locale): bool
    {
        return $locale === self::fallback();
    }

    /**
     * Bouw Tabs per locale met een schema-builder.
     *
     * Gebruik:
     * Tabs::make('Vertalingen')->tabs(
     *   I18nControls::tabs(fn (string $loc, bool $isFallback) => [
     *     TextInput::make("title.$loc")->required($isFallback),
     *     ...
     *   ])
     * )
     *
     * @param Closure(string $locale, bool $isFallback): array $schemaForLocale
     * @return array<Tab>
     */
    public static function tabs(Closure $schemaForLocale): array
    {
        $fallback = self::fallback();

        return collect(self::locales())
            ->map(fn (string $loc) =>
            Tab::make(strtoupper($loc))
                ->schema($schemaForLocale($loc, $loc === $fallback))
            )
            ->all();
    }

    /**
     * Standaard I18n UI-controls:
     * - Toggle: auto_translate
     * - CheckboxList: i18n_overrides.locked (gelockte talen)
     *
     * @return array<\Filament\Forms\Components\Component>
     */
    public static function make(): array
    {
        return [
            Toggle::make('auto_translate')
                ->label('Automatisch vertalen')
                ->default(true)
                ->helperText('Schakel uit om dit record volledig handmatig te beheren.'),

            Toggle::make('auto_translate')
                ->label('Automatisch vertalen na opslaan')
                ->helperText('Vult ontbrekende vertalingen aan voor de niet-fallback talen.')
                ->default(true)
                ->inline(false),

            CheckboxList::make('i18n_overrides.locked')
                ->label('Handmatig beheren (niet auto-vertalen)')
                ->options(self::options(markFallback: true))
                ->columns(2)
                ->helperText('Geselecteerde talen worden nooit automatisch overschreven.'),
        ];
    }
}
