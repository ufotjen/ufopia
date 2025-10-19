<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Tailwind + DaisyUI (CDN: daisyUI ships a compiled CSS that includes Tailwind utilities)
        Theme::updateOrCreate(
            ['key' => 'tailwind-daisyui'],
            [
                'version' => 'latest',
                'is_system' => true,
                'notes' => 'Tailwind + DaisyUI via CDN build',
                'config_schema' => [
                    'assets' => [
                        // Volledige DaisyUI build met Tailwind utilities
                        'css' => ['https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.css'],
                        'js'  => [],
                    ],
                    'map' => [
                        'button.primary'   => 'btn btn-primary',
                        'button.secondary' => 'btn btn-secondary',
                        'button.outline'   => 'btn btn-outline',
                        'card'             => 'card bg-base-100 shadow',
                        'alert.info'       => 'alert alert-info',
                        'alert.success'    => 'alert alert-success',
                        'alert.error'      => 'alert alert-error',
                        'prose'            => 'prose max-w-none',
                    ],
                ],
            ]
        );

        // 2) Materialize (Material Design-achtig framework)
        Theme::updateOrCreate(
            ['key' => 'materialize'],
            [
                'version' => '1.0.0',
                'is_system' => true,
                'notes' => 'Materialize CSS',
                'config_schema' => [
                    'assets' => [
                        'css' => ['https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css'],
                        'js'  => ['https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js'],
                    ],
                    'map' => [
                        'button.primary'   => 'btn waves-effect waves-light',
                        'button.secondary' => 'btn-flat',
                        'button.outline'   => 'btn-flat',
                        'card'             => 'card',
                        'alert.info'       => 'card-panel blue lighten-4',
                        'alert.success'    => 'card-panel green lighten-4',
                        'alert.error'      => 'card-panel red lighten-4',
                        'prose'            => 'flow-text',
                    ],
                ],
            ]
        );

        // 3) Bulma
        Theme::updateOrCreate(
            ['key' => 'bulma'],
            [
                'version' => '0.9',
                'is_system' => true,
                'notes' => 'Bulma CSS',
                'config_schema' => [
                    'assets' => [
                        'css' => ['https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css'],
                        'js'  => [],
                    ],
                    'map' => [
                        'button.primary'   => 'button is-primary',
                        'button.secondary' => 'button is-link is-light',
                        'button.outline'   => 'button is-outlined',
                        'card'             => 'card',
                        'alert.info'       => 'notification is-info',
                        'alert.success'    => 'notification is-success',
                        'alert.error'      => 'notification is-danger',
                        'prose'            => 'content',
                    ],
                ],
            ]
        );

        // 4) UIkit
        Theme::updateOrCreate(
            ['key' => 'uikit'],
            [
                'version' => '3',
                'is_system' => true,
                'notes' => 'UIkit 3',
                'config_schema' => [
                    'assets' => [
                        'css' => ['https://cdn.jsdelivr.net/npm/uikit@3.19.4/dist/css/uikit.min.css'],
                        'js'  => [
                            'https://cdn.jsdelivr.net/npm/uikit@3.19.4/dist/js/uikit.min.js',
                            'https://cdn.jsdelivr.net/npm/uikit@3.19.4/dist/js/uikit-icons.min.js',
                        ],
                    ],
                    'map' => [
                        'button.primary'   => 'uk-button uk-button-primary',
                        'button.secondary' => 'uk-button uk-button-default',
                        'button.outline'   => 'uk-button uk-button-default uk-button-small',
                        'card'             => 'uk-card uk-card-default uk-card-body',
                        'alert.info'       => 'uk-alert uk-alert-primary',
                        'alert.success'    => 'uk-alert uk-alert-success',
                        'alert.error'      => 'uk-alert uk-alert-danger',
                        'prose'            => 'uk-article',
                    ],
                ],
            ]
        );

        // 5) Foundation
        Theme::updateOrCreate(
            ['key' => 'foundation'],
            [
                'version' => '6.8',
                'is_system' => true,
                'notes' => 'ZURB Foundation 6',
                'config_schema' => [
                    'assets' => [
                        'css' => ['https://cdn.jsdelivr.net/npm/foundation-sites@6.8.1/dist/css/foundation.min.css'],
                        'js'  => ['https://cdn.jsdelivr.net/npm/foundation-sites@6.8.1/dist/js/foundation.min.js'],
                    ],
                    'map' => [
                        'button.primary'   => 'button primary',
                        'button.secondary' => 'button secondary',
                        'button.outline'   => 'hollow button',
                        'card'             => 'card',
                        'alert.info'       => 'callout primary',
                        'alert.success'    => 'callout success',
                        'alert.error'      => 'callout alert',
                        'prose'            => 'callout',
                    ],
                ],
            ]
        );

        // 6) Semantic UI (via Fomantic-UI community build)
        Theme::updateOrCreate(
            ['key' => 'semantic-ui'],
            [
                'version' => '2.9 (Fomantic)',
                'is_system' => true,
                'notes' => 'Semantic UI (Fomantic-UI)',
                'config_schema' => [
                    'assets' => [
                        'css' => ['https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.css'],
                        'js'  => ['https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.3/dist/semantic.min.js'],
                    ],
                    'map' => [
                        'button.primary'   => 'ui primary button',
                        'button.secondary' => 'ui secondary button',
                        'button.outline'   => 'ui basic button',
                        'card'             => 'ui card',
                        'alert.info'       => 'ui message info',
                        'alert.success'    => 'ui message positive',
                        'alert.error'      => 'ui message negative',
                        'prose'            => 'ui content',
                    ],
                ],
            ]
        );
    }
}
