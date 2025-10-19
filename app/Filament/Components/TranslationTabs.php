<?php

namespace App\Filament\Components;

use App\Filament\Forms\Components\Reusable\I18nControls;
use Closure;

// FORMS (Schema API)
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\{TextInput, Textarea, RichEditor, KeyValue};

// INFOLISTS
use Filament\Infolists\Components\TextEntry;

// TABLES
use Filament\Tables\Columns\TextColumn;

class TranslationTabs
{

    private static function getLocalized($record, string $field, string $loc): ?string
    {
        // 1) Spatie HasTranslations
        if (method_exists($record, 'getTranslation')) {
            $v = $record->getTranslation($field, $loc, false); // géén fallback
            if ($v !== null && $v !== '') {
                return (string) $v;
            }
        }

        // 2) Ruwe attribute
        $raw = data_get($record, $field);

        // 2a) Als het een JSON-string is
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $val = $decoded[$loc] ?? null;
                return ($val !== '' ? (string) $val : null);
            }
            // 2b) Plain string (geen per-locale structuur)
            return $raw !== '' ? $raw : null;
        }

        // 2c) Als het een array is met per-locale keys
        if (is_array($raw)) {
            $val = $raw[$loc] ?? null;
            return ($val !== '' ? (string) $val : null);
        }

        return null;
    }

    /**
     * FORM: lokalisatie-tabs (Schemas API).
     *
     * @param array<string>|null $fields
     * @param Closure(string $locale, bool $isFallback): (array<>)|null $schemaForLocale
     * @param array<string,string> $componentMap  e.g. ['content'=>'rich','excerpt'=>'textarea','seo'=>'key-value']
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
     * @param array<string,string> $componentMap e.g. ['content'=>'html','seo'=>'json']
     * @param string $layout
     * @return array<>
     */
    public static function infolist(
        ?array $fields = null,
        array $componentMap = [],
        string $layout = 'tabs' // 'tabs' (bestaand gedrag) of 'matrix' (nieuw)
    ): array {
        $translatable = $fields ?? static::detectTranslatableFields();
        $locales     = I18nControls::locales();
        $fallback    = I18nControls::fallback();

        if ($layout === 'tabs') {
            // ... laat je bestaande tab-rendering ongemoeid
            // (niet opnieuw plakken hier)
            return static::renderTabsInfolist($translatable, $componentMap);
        }

        // ── MATRIX ───────────────────────────────────────────────────────────────
        // We tonen per attribuut een sub-section met kolommen = #locales.
        $out = [];

        foreach ($translatable as $field) {
            $row = [];

            foreach ($locales as $loc) {
                $locLabel = strtoupper($loc) . ($loc === $fallback ? ' • default' : '');

                $row[] = TextEntry::make("i18n.$field.$loc")
                    ->label($locLabel)              // kolomkop boven de waarde
                    ->placeholder('—')
                    ->getStateUsing(fn ($record) => static::getLocalized($record, $field, $loc))
                    ->columnSpan(1);
            }

            // één nette rij per attribuut, zonder “hdr_*” cel
            $out[] = Section::make(ucfirst(str_replace('_',' ',$field)))
                ->columns(count($locales))
                ->schema($row)
                ->compact()            // oogt strakker
                ->collapsible(false);  // altijd open
        }

        return $out;
    }

    /**
     * TABLE: kolommen voor huidige locale.
     *
     * @param array<string>|null $fields
     * @param array<string,string> $componentMap  e.g. ['slug'=>'short','excerpt'=>'wrap']
     * @param bool $showCompleteness  Zet dit enkel aan waar je het echt wil.
     * @return array<Column>
     */
    public static function table(?array $fields = null, array $componentMap = [], bool $showCompleteness = true): array
    {
        $translatable = $fields ?? static::detectTranslatableFields();
        $loc = app()->getLocale();

        $cols = [];
        if (!empty($translatable)) {
            foreach ($translatable as $field) {
                $label = ucfirst(str_replace('_', ' ', $field));

                $col = TextColumn::make("i18n.$field.$loc")   // virtuele key, puur om uniek te zijn
                ->label($label)
                    ->toggleable()
                    ->searchable(false) // zoeken op JSON kan je apart regelen indien gewenst
                    ->getStateUsing(fn ($record) => static::getLocalized($record, $field, $loc));

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
                        $locales = (array)config('app.supported_locales', ['en', 'nl', 'fr', 'de']);
                        $fallback = (string)config('app.fallback_locale', 'en');

                        $missing = 0;

                        foreach ($translatable as $field) {
                            $vals = method_exists($record, 'getTranslations')
                                ? (array)$record->getTranslations($field)
                                : (array)data_get($record, $field, []);

                            foreach ($locales as $l) {
                                if ($l === $fallback) continue;
                                if (!isset($vals[$l]) || $vals[$l] === '') $missing++;
                            }
                        }

                        return $missing === 0 ? '✅ OK' : "⛔ $missing open";
                    })
                    ->color(fn($state) => str_starts_with((string)$state, '✅') ? 'success' : 'warning')->sortable(false)
                    ->toggleable();
            }
        }
        return $cols;
    }

    protected static function detectTranslatableFields(Model $record = null, array $fallback = []): array
    {
        $record ??= request()->route('record');

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
        return $fallback;// ['title','excerpt','content','slug','meta_title','meta_description','seo','team_meta','tagline','bio','label'];
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

    private static function renderTabsInfolist(array $translatable, array $componentMap): array
    {
        $locales  = I18nControls::locales();
        $fallback = I18nControls::fallback();

        $out = [];

        foreach ($locales as $loc) {
            $tabLabel = strtoupper($loc) . ($loc === $fallback ? ' • default' : '');
            $entries  = [];

            foreach ($translatable as $field) {
                $label = ucfirst(str_replace('_', ' ', $field));

                $entry = TextEntry::make("i18n.$field.$loc")
                    ->label($label)
                    ->placeholder('—')
                    ->getStateUsing(fn ($record) =>
                    static::getLocalized($record, $field, $loc));

                // optionele render-modi
                $mode = $componentMap[$field] ?? null;
                if ($mode === 'html') {
                    $entry = $entry->html();
                } elseif ($mode === 'json') {
                    $entry = $entry
                        ->formatStateUsing(fn ($record) => static::getLocalized($record, $field, $loc))
                        ->copyable();
                }

                $entries[] = $entry;
            }

            // Eén “tab” als Section (in Infolist rendert dat onder elkaar)
            $out[] = Section::make($tabLabel)->columns(1)->schema($entries);
        }

        return $out;
    }
}
