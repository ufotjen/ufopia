<?php

namespace App\Models\Traits;

use App\Contracts\TranslateContract;
use App\Support\I18nHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;


/**
 * Zorgt voor automatische vertaling van translatable velden na save().
 *
 * @method array getTranslations(string $key)
 * @method static setTranslations(string $key, array $translations)
 */
trait AutoTranslate
{
    use I18nHelper;
    protected static function bootAutoTranslate(): void
    {
        static::registerModelEvent('saved', function (Model $model) {
            // Globaal uit?
            if (! config('translation.auto')) return;

            // Alleen modellen met HasTranslations
            if (! in_array(HasTranslations::class, class_uses_recursive($model), true)) return;

            // Safety: methodes bestaan echt (helpt IDE + edge cases)
            if (! method_exists($model, 'getTranslations') || ! method_exists($model, 'setTranslations')) return;

            // Per-record toggle
            if ($model->getAttribute('auto_translate') === false) return;

            // Zonder binding: netjes stoppen (contract behouden)
            if (! app()->bound(TranslateContract::class)) return;
            /** @var TranslateContract $translator */
            $translator = app(TranslateContract::class);

            $locales  = (array) config('app.supported_locales', ['en','nl','fr','de']);
            $fallback = (string) config('app.fallback_locale', 'en');

            // Locked locales
            $locked = [];
            if (method_exists($model, 'lockedLocales')) {
                $locked = (array) $model->lockedLocales();
            } else {
                $overrides = (array) ($model->getAttribute('i18n_overrides') ?? []);
                if (isset($overrides['locked']) && is_array($overrides['locked'])) {
                    $locked = array_values($overrides['locked']);
                }
            }

            // Translatable velden
            $fields = method_exists($model, 'getTranslatableAttributes')
                ? $model->getTranslatableAttributes()
                : (property_exists($model, 'translatable') ? (array) $model->translatable : []);
            if (empty($fields)) return;

            // Slugvelden
            $slugFields =
                method_exists($model, 'slugFields') ? (array) $model->slugFields() :
                    (property_exists($model, 'slugFields') ? (array) $model->slugFields :
                        (array) config('translation.slug_fields', ['slug']));

            $dirty = false;

            foreach ($fields as $field) {
                $isSlug = in_array($field, $slugFields, true);

                $vals = (array) $model->getTranslations($field);
                $src  = $vals[$fallback] ?? null;
                if ($src === null || $src === '') continue;

                foreach ($locales as $loc) {
                    if ($loc === $fallback) continue;
                    if (in_array($loc, $locked, true)) continue;
                    if (($vals[$loc] ?? '') !== '') continue;

                    $translated = $translator->translate($src, $fallback, $loc, ['field' => $field]);

                    if ($isSlug) {
                        $slug = Str::slug($translated ?: $src);
                        $vals[$loc] = static::uniqueLocalizedSlug($model, $field, $loc, $slug);
                    } else {
                        $vals[$loc] = $translated ?: $src;
                    }

                    $dirty = true;
                }

                if ($isSlug && empty($vals[$fallback])) {
                    $vals[$fallback] = static::uniqueLocalizedSlug(
                        $model, $field, $fallback, Str::slug($src )
                    );
                    $dirty = true;
                }

                $vals = self::sanitizeTranslations($vals, $locales);
                $model->setTranslations($field, $vals);
            }

            if ($dirty) {
                $model->saveQuietly(); // voorkom event-loop
            }
        });
    }

    protected static function uniqueLocalizedSlug(Model $model, string $field, string $locale, string $base): string
    {
        $slug = $base;
        $i = 2;

        while (
        $model->newQuery()
            ->where("$field->$locale", $slug)
            ->when($model->exists, fn ($q) => $q->whereKeyNot($model->getKey()))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
