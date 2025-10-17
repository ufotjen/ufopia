<?php

namespace App\Enums;

enum TranslationProviders: string
{
    case DeepL = 'deepl';
    case OpenAI = 'openai';
}
