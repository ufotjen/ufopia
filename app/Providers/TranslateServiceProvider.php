<?php

namespace App\Providers;

use App\Contracts\TranslateContract;
use App\Services\Translate\DeepLTranslator;
use App\Services\Translate\OpenAiTranslator;
use App\Services\Translate\SmartTranslator;
use DeepL\DeepLClient;
use Illuminate\Support\ServiceProvider;

class TranslateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TranslateContract::class, function () {
            $deepl = null;
            if ($key = config('services.deepl.key')) {
                $deepl = new DeepLTranslator(new DeepLClient($key));
            }

            $openai = null;
            if ($okey = config('services.openai.key')) {
                $openai = new OpenAITranslator($okey, config('services.openai.model', 'gpt-4o-mini'));
            }

            // SmartTranslator kiest per veld/lengte en fallbackt
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
