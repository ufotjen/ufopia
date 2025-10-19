<?php

namespace App\Support;

trait I18nHelper
{
    static function sanitizeTranslations(array $vals, array $locales): array
    {
        $allowed = array_flip($locales);
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
