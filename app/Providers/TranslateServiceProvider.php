<?php

namespace App\Providers;

use App\Contracts\TranslateContract;
use App\Services\Translate\DeepLTranslator;
use App\Services\Translate\OpenAiTranslator;
use App\Services\Translate\SmartTranslator;
use DeepL\Translator;
use Illuminate\Support\ServiceProvider;

class TranslateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TranslateContract::class, function () {
            // Keys kunnen uit config/services.php of uit config/translation.php komen
            $deeplKey   = config('services.deepl.key')    ?? config('translation.deepl.key');
            $openaiKey  = config('services.openai.key')   ?? config('translation.openai.key');
            $openaiModel= config('services.openai.model') ?? config('translation.openai.model', 'gpt-4o-mini');

            $deepl = null;
            if ($deeplKey) {
                // DeepL SDK verwacht DeepL\Translator
                $deepl = new DeepLTranslator(new Translator($deeplKey));
            }

            $openai = null;
            if ($openaiKey) {
                // Gebruik je eigen klassenaam precies zoals gedefinieerd: OpenAiTranslator
                $openai = new OpenAiTranslator($openaiKey, $openaiModel);
            }

            // Slimme router met fallback (OpenAI ⇄ DeepL)
            return new SmartTranslator($deepl, $openai);
        });
    }
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
