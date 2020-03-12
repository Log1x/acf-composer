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
    | Sidebar Widgets
    |--------------------------------------------------------------------------
    |
    | The widgets listed here will be automatically loaded on the
    | request to your application.
    |
    */

    'widgets' => [
        // App\Widgets\Example::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Field Type Settings
    |--------------------------------------------------------------------------
    |
    | Here you can set default field group and field type configuration that
    | is then merged with your field groups when they are composed.
    |
    | This allows you to avoid the repetitive process of setting common field
    | configuration such as `ui` on every `trueFalse` field or
    | `instruction_placement` on every `fieldGroup`.
    |
    */

    'defaults' => [
        'trueFalse' => ['ui' => 1],
        'select' => ['ui' => 1],
    ],
];
