<?php

namespace App\Services\Translate;

use App\Enums\TranslationProviders;

class Router
{

    /**
     * @param string $text
     * @return TranslationProviders
     */
    public static function decide(string $text): TranslationProviders
    {
        $plain        = trim(strip_tags($text));
        $len          = mb_strlen($plain);
        $minChars     = (int) config('translation.semantic_min_chars', 400);
        $minWords     = (int) config('translation.semantic_min_words', 60);
        $wordCount    = preg_match_all('/\p{L}+/u', $plain, $m);
        $hasHtml      = $plain !== $text;
        $hasSentences = (bool) preg_match('/[.!?](\s|$)/u', $plain);

        if ($len >= $minChars || $wordCount >= $minWords || $hasHtml || $hasSentences) {
            return TranslationProviders::OpenAI;   // lange/semantische tekst
        }
        return TranslationProviders::DeepL;        // kort/letterlijk
    }
}
