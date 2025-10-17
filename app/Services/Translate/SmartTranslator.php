<?php

namespace App\Services\Translate;

use App\Enums\TranslationProviders;
use App\Enums\TranslationProviders as tp;
use App\Contracts\TranslateContract;

class SmartTranslator implements TranslateContract
{

    public function __construct(
        private readonly ?TranslateContract $deepl,
        private readonly ?TranslateContract $openai,
    ) {}


    public function translate(?string $text, string $from, string $to, array $ctx = []): ?string
    {
        if ($text === null || $text === '') return $text;
        if (!$this->deepl && !$this->openai) return $text;

        $choice = Router::decide($text);               // ← enum
        $useOpenAI = ($choice === TranslationProviders::OpenAI);

        $primary  = $useOpenAI ? $this->openai : $this->deepl;
        $fallback = $useOpenAI ? $this->deepl  : $this->openai;

        foreach ([$primary, $fallback] as $svc) {
            if (!$svc) continue;
            try {
                $out = $svc->translate($text, $from, $to, $ctx);
                if ($out !== null && $out !== '') return $out;
            } catch (\Throwable) {
                // optioneel: \Log::warning('translate failed', [...]);
            }
        }

        return $text; // laatste redmiddel
    }
}
