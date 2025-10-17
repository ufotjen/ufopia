<?php

namespace App\Contracts;

/**
 * Eenvoudig vertaalcontract voor je service-laag.
 * $ctx kan bv. ['field' => 'content'] bevatten zodat je router per veld kan kiezen (DeepL vs OpenAI).
 */
interface TranslateContract
{
    /**
     * @param string|null $text  Bron (plain of HTML)
     * @param string      $from  Brontaal (bv. 'nl')
     * @param string      $to    Doeltaal (bv. 'en')
     * @param array       $ctx   Extra context (bv. ['field' => 'title'])
     * @return string|null       Vertaalde tekst of null bij lege input
     */
    public function translate(?string $text, string $from, string $to, array $ctx = []): ?string;
}
