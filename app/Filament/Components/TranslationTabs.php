<?php

namespace App\Filament\Components;

use App\Filament\Forms\Components\Reusable\I18nControls;
use Closure;

// FORMS (Schema API)
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\{TextInput, Textarea, RichEditor, KeyValue};

// INFOLISTS
use Filament\Infolists\Components\TextEntry;

// TABLES
use Filament\Tables\Columns\TextColumn;

class TranslationTabs
{
    /**
     * FORM: lokalisatie-tabs (Schemas API).
     *
     * @param array<string>|null $fields
     * @param Closure(string $locale, bool $isFallback): (array<\Filament\Forms\Components\Component>)|null $schemaForLocale
     * @param array<string,string> $componentMap  e.g. ['content'=>'rich','excerpt'=>'textarea','seo'=>'keyvalue']
     */
    public static function form(?array $fields = null, ?Closure $schemaForLocale = null, array $componentMap = []): Tabs
    {
        $translatable = $fields ?? static::detectTranslatableFields();

        return Tabs::make('Vertalingen')->tabs(
            collect(I18nControls::locales())->map(function (string $locale) use ($translatable, $schemaForLocale, $componentMap) {
                $title = strtoupper($locale) . ($locale === I18nControls::fallback() ? ' (default)' : '');
                $components = [];

                foreach ($translatable as $field) {
                    $label = ucfirst(str_replace('_', ' ', $field));
                    $type  = $componentMap[$field] ?? null;

                    if ($type === 'keyvalue') {
                        $components[] = KeyValue::make("$field.$locale")
                            ->label($label)
                            ->keyLabel('key')
                            ->valueLabel('value')
                            ->reorderable(false)
                            ->addActionLabel('Toevoegen');
                        continue;
                    }

                    if ($type === 'rich' || in_array($field, ['content', 'body', 'description'], true)) {
                        $components[] = RichEditor::make("$field.$locale")->label($label)->columnSpanFull();
                        continue;
                    }

                    if ($type === 'textarea' || in_array($field, ['excerpt', 'meta_description'], true)) {
                        $components[] = Textarea::make("$field.$locale")->label($label)->rows(3);
                        continue;
                    }

                    $components[] = TextInput::make("$field.$locale")->label($label);
                }

                if ($schemaForLocale) {
                    $extra = $schemaForLocale($locale, $locale === I18nControls::fallback());

                    if (is_array($extra)) {
                        foreach ($extra as $x) {
                            // laat uitsluitend échte Forms components toe (geen arrays/containers/anders)
                            if (is_object($x) && str_starts_with($x::class, 'Filament\\Forms\\Components\\')) {
                                $components[] = $x;
                            }
                        }
                    }
                }

                return Tab::make($title)
                    ->icon('heroicon-o-language')
                    ->schema([
                        // columns() default is 2 in 4.1; laten we die implicit houden
                        Section::make('Vertalingen')->schema($components),
                    ]);
            })->all()
        );
    }

    /**
     * INFOLIST: géén containers – alleen entries.
     *
     * @param array<string>|null $fields
     * @param Closure(string $locale, bool $isFallback): (array<\Filament\Infolists\Components\Component>)|null $schemaForLocale
     * @param array<string,string> $componentMap  e.g. ['content'=>'html','seo'=>'json']
     * @return array<\Filament\Infolists\Components>
     */
    public static function infolist(?array $fields = null, ?\Closure $schemaForLocale = null, array $componentMap = []): array
    {
        $translatable = $fields ?? ['title','excerpt','content','slug','meta_title','meta_description','seo','team_meta'];
        $entries = [];

        $locales  = I18nControls::locales();
        $fallback = I18nControls::fallback();

        foreach ($locales as $locale) {
            foreach ($translatable as $field) {
                $label = ucfirst(str_replace('_', ' ', $field)) . ' (' . strtoupper($locale)
                    . ($locale === $fallback ? ' • default' : '') . ')';

                $mode  = $componentMap[$field] ?? null;

                $entry = TextEntry::make("$field.$locale")
                    ->label($label)
                    ->placeholder('—');

                if ($mode === 'html' || in_array($field, ['content','body','description'], true)) {
                    $entry = $entry->html();
                } elseif ($mode === 'json' || in_array($field, ['seo','team_meta'], true)) {
                    $entry = $entry->formatStateUsing(fn ($v) => is_null($v) ? '—' : json_encode(
                        is_string($v) ? json_decode($v, true) ?? $v : $v,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR
                    ));
                }

                $entries[] = $entry;
            }

            if ($schemaForLocale) {
                $extra = $schemaForLocale($locale, $locale === $fallback);

                if (is_array($extra)) {
                    foreach ($extra as $x) {
                        // Guard: uitsluitend Infolist components
                        if (is_object($x) && str_starts_with($x::class, 'Filament\\Infolists\\Components\\')) {
                            $entries[] = $x;
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * TABLE: kolommen voor huidige locale.
     *
     * @param array<string>|null $fields
     * @param array<string,string> $componentMap  e.g. ['slug'=>'short','excerpt'=>'wrap']
     * @param bool $showCompleteness  Zet dit enkel aan waar je het echt wil.
     * @return array<\Filament\Tables\Columns\Column>
     */
    public static function table(?array $fields = null, array $componentMap = [], bool $showCompleteness = false): array
    {
        $translatable = $fields ?? static::detectTranslatableFields();
        $loc = app()->getLocale();

        $cols = [];
        foreach ($translatable as $field) {
            $label = ucfirst(str_replace('_', ' ', $field));
            $col = TextColumn::make("$field.$loc")->label($label)->toggleable()->searchable();

            if (($componentMap[$field] ?? null) === 'wrap' || $field === 'excerpt') {
                $col = $col->wrap();
            }
            if (($componentMap[$field] ?? null) === 'short' || in_array($field, ['slug', 'meta_title'], true)) {
                $col = $col->limit(40);
            }

            $cols[] = $col;
        }

        if ($showCompleteness) {
            $cols[] = TextColumn::make('i18n_completeness')
                ->label('Vertalingen')
                ->badge()
                ->getStateUsing(function ($record) use ($translatable) {
                    $locales  = I18nControls::locales();
                    $fallback = I18nControls::fallback();
                    $missing = 0;

                    foreach ($translatable as $field) {
                        $vals = (array) data_get($record, $field, []);
                        foreach ($locales as $l) {
                            if ($l === $fallback) continue;
                            if (!isset($vals[$l]) || $vals[$l] === '' || $vals[$l] === null) {
                                $missing++;
                            }
                        }
                    }
                    return $missing === 0 ? '✅ OK' : "⛔ $missing open";
                })
                ->color(fn(string $state) => str_starts_with($state, '✅') ? 'success' : 'warning')
                ->sortable(false)
                ->toggleable()
                ->searchable();
        }

        return $cols;
    }

    protected static function detectTranslatableFields(): array
    {
        $record = request()->route('record');

        if ($record instanceof Model) {
            if (method_exists($record, 'getTranslatableAttributes')) {
                return $record->getTranslatableAttributes();
            }

            if (property_exists($record, 'translatable')) {
                /** @var array $t */
                $t = $record->translatable;
                return array_values($t);
            }
        }

        // Fallbacks die we vaak gebruiken in je project
        return ['title','excerpt','content','slug','meta_title','meta_description','seo','team_meta','tagline','bio','label'];
    }
    /** Robuuste JSON formatter zonder diepe/cyclische structs. */
    protected static function safeJson(mixed $v): string
    {
        if (is_string($v)) return $v;
        if ($v === null) return '—';
        if (is_scalar($v)) return (string) $v;

        $sanitized = static::sanitizeForJson($v, 0);

        return json_encode(
            $sanitized,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR
        ) ?: '—';
    }

    /** Beperk diepte & grootte; voorkom cycli/objecten. */
    protected static function sanitizeForJson(mixed $v, int $depth): mixed
    {
        if ($depth >= 3) return '[…]';

        if (is_array($v)) {
            $out = [];
            $i = 0;
            foreach ($v as $k => $val) {
                if ($i++ >= 50) {
                    $out['…'] = 'truncated';
                    break;
                }
                $out[$k] = static::sanitizeForJson($val, $depth + 1);
            }
            return $out;
        }

        if (is_object($v)) {
            if (method_exists($v, 'toArray')) {
                return static::sanitizeForJson($v->toArray(), $depth + 1);
            }
            if (method_exists($v, 'jsonSerialize')) {
                /** @var mixed $serializable */
                $serializable = $v->jsonSerialize();
                return static::sanitizeForJson($serializable, $depth + 1);
            }
            return '[' . $v::class . ']';
        }

        return $v;
    }
}
