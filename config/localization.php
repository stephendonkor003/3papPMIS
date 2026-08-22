<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported application locales
    |--------------------------------------------------------------------------
    |
    | Keep locale metadata in one place so middleware, controllers and views
    | cannot silently drift apart when a language is added or renamed.
    |
    */
    'supported' => [
        'en' => [
            'short' => 'EN',
            'name' => 'English',
            'direction' => 'ltr',
        ],
        'fr' => [
            'short' => 'FR',
            'name' => 'Français',
            'direction' => 'ltr',
        ],
        'ar' => [
            'short' => 'AR',
            'name' => 'العربية',
            'direction' => 'rtl',
        ],
        'pt' => [
            'short' => 'PT',
            'name' => 'Português',
            'direction' => 'ltr',
        ],
        'es' => [
            'short' => 'ES',
            'name' => 'Español',
            'direction' => 'ltr',
        ],
        'sw' => [
            'short' => 'SW',
            'name' => 'Kiswahili',
            'direction' => 'ltr',
        ],
    ],
];
