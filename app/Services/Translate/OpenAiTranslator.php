<?php

namespace App\Services\Translate;

use App\Contracts\TranslateContract;
use OpenAI;

class OpenAiTranslator implements TranslateContract
{
    public function __construct(private string $apiKey, private string $model = 'gpt-4o-mini') {}

    public function translate(?string $text, string $from, string $to, array $ctx = []): ?string
    {
        if (!$text) return $text;

        $client = OpenAI::client($this->apiKey);
        $prompt = "Translate from {$from} to {$to}. Preserve HTML tags. Only return translated text.\n\n".$text;

        $resp = $client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role'=>'system','content'=>'You translate text, preserving HTML and intent.'],
                ['role'=>'user','content'=>$prompt],
            ],
        ]);

        return trim($resp->choices[0]->message->content ?? '');
    }
}
