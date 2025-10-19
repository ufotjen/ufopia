<?php

namespace App\Support;

trait I18nHelper
{
    static function sanitizeTranslations(array $vals, array $locales): array
    {
        $allowed = array_flip($locales );
        $vals = array_filter( $vals, fn($v, $k) => is_string($k) && isset($allowed[$k]), ARRAY_FILTER_USE_BOTH);
        $clean = [];

        foreach ($vals as $k => $v) {
            // Sla alleen string keys op die een geldige locale zijn
            if (is_string($k) && isset($allowed[$k])) {
                $clean[$k] = ($v === null) ? '' : (is_scalar($v) ? (string) $v : (string) $v);
            }
        }

        return $clean;
    }
}
