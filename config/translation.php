<?php
return [
    'slug_fields' => ['slug'],
    'semantic_min_words' => 60,
    // Default provider als niets matcht
    'provider' => env('TRANSLATION_PROVIDER', 'deepl'),

    // Automatisch vertalen?
    'auto' => (bool)env('TRANSLATION_AUTO', true),

    // Doeltalen (CSV → explode in code)
    'locales' => env('TRANSLATION_LOCALES', 'en,nl,fr,de'),

    // Veld-gebaseerde routering
    'semantic_fields' => [ // deze velden via OpenAI (semantisch)
        'content', 'body', 'excerpt', 'description',
    ],
    // Lengte-drempel (als een veld langer is dan dit → OpenAI)
    'semantic_min_chars' => 400,

    'deepl' => [
        'key' => env('DEEPL_API_KEY'),
        'free' => (bool)env('DEEPL_FREE', false),

        // Forceer specifieke DeepL codes per locale (optioneel)
        // bv. 'en' => 'EN-US' of 'en-GB'
        'lang_map' => [
            // 'en' => 'EN-GB',
            // 'pt' => 'PT-BR',
            // 'zh' => 'ZH-HANT',
        ],

        // Als alleen 'en'/'pt'/'zh' gegeven is, kies dan deze varianten voor 'target'
        'variants' => [
            'nl' => 'NL',
            'de' => 'DE',
            'en' => 'EN-GB',
            'fr' => 'FR',
        ],
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_TRANSLATE_MODEL', 'gpt-4o-mini'),
    ],
];
