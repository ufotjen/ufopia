<?php

namespace App\Models\Traits;

use App\Contracts\TranslateContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** @mixin Model */

trait AutoTranslate
{
    protected static function bootAutoTranslates(): void
    {
        static::registerModelEvent('saved', function ($model) {
            if (!config('translation.auto')) return;

            $fields = method_exists($model, 'getTranslatableAttributes')
                ? $model->getTranslatableAttributes()
                : ((property_exists($model, 'translatable') ? (array) $model->translatable : []));

            if (!$fields) return;

            $slugFields =
                method_exists($model, 'slugFields') ? (array) $model->slugFields() :
                    (property_exists($model, 'slugFields') ? (array) $model->slugFields :
                        (array) config('translation.slug_fields', ['slug']));

            $locales  = array_map('trim', explode(',', (string) config('translation.locales','en,nl,fr,de')));
            $fallback = (string) config('app.fallback_locale','en');

            /** @var TranslateContract $translator */
            $translator = app(TranslateContract::class);

            $dirty = false;

            foreach ($fields as $field) {
                $isSlug = in_array($field, $slugFields, true);

                $vals = (array) $model->getAttribute($field);
                $src  = $vals[$fallback] ?? null;
                if (!$src) continue;

                foreach ($locales as $loc) {
                    if ($loc === $fallback) continue;
                    if (($vals[$loc] ?? '') !== '') continue;

                    $translated = $translator->translate($src, $fallback, $loc, ['field'=>$field]);

                    if ($isSlug) {
                        $slug = Str::slug($translated ?: $src, '-');
                        $vals[$loc] = static::uniqueLocalizedSlug($model, $field, $loc, $slug);
                    } else {
                        $vals[$loc] = $translated ?: $src;
                    }
                    $dirty = true;
                }

                if ($isSlug && empty($vals[$fallback])) {
                    $vals[$fallback] = static::uniqueLocalizedSlug($model, $field, $fallback, Str::slug($src, '-'));
                    $dirty = true;
                }

                $model->setAttribute($field, $vals);
            }

            if ($dirty) $model->saveQuietly();
        });
    }

    protected static function uniqueLocalizedSlug($model, string $field, string $locale, string $base): string
    {
        $slug = $base; $i = 2;
        while ($model->newQuery()
            ->where($field.'->'.$locale, $slug)
            ->when($model->exists, fn($q) => $q->whereKeyNot($model->getKey()))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}
