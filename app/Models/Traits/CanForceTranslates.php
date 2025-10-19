<?php

namespace App\Models\Traits;

use App\Contracts\TranslateContract;
use App\Support\I18nHelper;

trait CanForceTranslates
{
    use I18nHelper;

    /**
     * @param array<string>|null $locales  null = alle uit config('translation.locales')
     * @param bool $overwrite  true = ook bestaande vertalingen overschrijven
     * @return int  aantal ingevulde velden
     */
    public function forceTranslate(?array $locales = null, bool $overwrite = false): int
    {
        /** @var TranslateContract $translator */
        $translator = app(TranslateContract::class);

        $fields   = method_exists($this, 'getTranslatableAttributes')
            ? $this->getTranslatableAttributes()
            : (property_exists($this, 'translatable') ? (array) $this->translatable : []);

        if (! $fields) return 0;

        $fallback = (string) config('app.fallback_locale', 'en');
        $targets  = $locales ?: array_map('trim', explode(',', (string) config('translation.locales', 'en,nl,fr,de')));

        $filled = 0;

        foreach ($fields as $field) {
            $vals = (array) $this->getTranslations($field);
            $src  = $vals[$fallback] ?? null;
            if (! $src) continue;

            foreach ($targets as $to) {
                if ($to === $fallback) continue;
                $has = ($vals[$to] ?? '') !== '';
                if ($has && ! $overwrite) continue;

                $out = $translator->translate($src, $fallback, $to, ['field' => $field]);
                if ($out !== null && $out !== '') {
                    $vals[$to] = $out;
                    $filled++;
                }
            }
            $vals = self::sanitizeTranslations($vals, $targets);
            $this->setTranslations($field, $vals);
        }

        if ($filled > 0) {
            $this->saveQuietly(); // geen loops triggeren
        }

        return $filled;
    }
}
