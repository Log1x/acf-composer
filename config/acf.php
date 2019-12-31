<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Standard Fields
    |--------------------------------------------------------------------------
    |
    | The fields listed here will be automatically loaded on the
    | request to your application.
    |
    */

    'fields' => [
        // App\Fields\Example::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Gutenberg Blocks
    |--------------------------------------------------------------------------
    |
    | The Gutenberg blocks listed here will be automatically loaded on the
    | request to your application.
    |
    */

    'blocks' => [
        // App\Blocks\Example::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Field Type Settings
    |--------------------------------------------------------------------------
    |
    | Here you can set default field type settings that are automatically
    | applied to the field types when built.
    |
    */

    'defaults' => [
        'trueFalse' => ['ui' => 1],
    ],
];
