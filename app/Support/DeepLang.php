<?php

namespace App\Support;

class DeepLang
{
    public static function map(?string $locale, bool $target = true): ?string
    {
        if (!$locale) return null;

        $locale = str_replace('_', '-', strtolower($locale));

        // 1) directe override
        $override = config("translation.deepl.lang_map.$locale")
            ?? config("translation.deepl.lang_map." . explode('-', $locale)[0]);
        if ($override) return strtoupper(str_replace('_', '-', $override));

        // 2) als region aanwezig is: neem die
        if (str_contains($locale, '-')) {
            return strtoupper($locale); // bv. en-us → EN-US
        }

        // 3) varianten voor targets (en/pt/zh)
        if ($target) {
            $primary  = $locale; // 'en' | 'pt' | 'zh' | ...
            $variant  = config("translation.deepl.variants.$primary");
            if ($variant) return strtoupper($variant);
        }

        // 4) default: twee-letter code in caps (EN, NL, FR, DE, …)
        return strtoupper($locale);
    }
}
