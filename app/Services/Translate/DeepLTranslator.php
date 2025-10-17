<?php

namespace App\Services\Translate;

use App\Contracts\TranslateContract;
use App\Support\DeepLang;
use DeepL\Translator;

class DeepLTranslator implements TranslateContract
{

    public function __construct(private Translator $client) {}

    /**
     * @inheritDoc
     */
    public function translate(?string $text, string $from, string $to, array $ctx = []): ?string
    {
        // 1) Niets te vertalen
        if ($text === null || $text === '') {
            return $text;
        }

        // 2) Taalcodes normaliseren (source mag null zijn: auto-detect)
        $fromCode = DeepLang::map($from, target: false);   // bv. null of "NL", "EN-US"
        $toCode   = DeepLang::map($to,   target: true);    // bv. "NL", "EN-GB", "FR", ...

        // Safety: zonder geldige doeltaal doen we niets
        if (!$toCode) {
            return $text;
        }

        // 3) Opties opbouwen
        $options = [
            'tagHandling' => 'html',           // behoud HTML-structuur
            // 'outlineDetection' => true,     // (optioneel) betere zinsdetectie
            // 'preserveFormatting' => true,   // (optioneel) respecteer harde line breaks
        ];

        // (Optioneel) glossary ID doorgeven (uit $ctx of uit config)
        $glossaryId = $ctx['glossary_id'] ?? config('translation.deepl.glossary_id');
        if (!empty($glossaryId)) {
            $options['glossary'] = $glossaryId;
        }

        // 4) API-call
        // DeepL accepteert string of array; we houden het hier bij string.
        $result = $this->client->translateText($text, $fromCode, $toCode, $options);

        // 5) Antwoord normaliseren
        // SDK kan 1 object of array van objecten teruggeven:
        if (is_array($result)) {
            return $result[0]->text ?? $text;
        }

        return $result->text ?? $text;
    }
}
