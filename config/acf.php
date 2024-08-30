<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Field Type Settings
    |--------------------------------------------------------------------------
    |
    | Here you can set default field group and field type configuration that
    | is then merged with your field groups when they are composed.
    |
    | This allows you to avoid the repetitive process of setting common field
    | configuration such as `ui` on every `trueFalse` field or your
    | preferred `instruction_placement` on every `fieldGroup`.
    |
    */

    'defaults' => [
        // 'trueFalse' => ['ui' => 1],
        // 'select' => ['ui' => 1],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Field Types
    |--------------------------------------------------------------------------
    |
    | Here you can define custom field types that are not included with ACF
    | out of the box. This allows you to use the fluent builder pattern with
    | custom field types such as `addEditorPalette()`.
    |
    */

    'types' => [
        // 'editorPalette' => 'editor_palette',
        // 'phoneNumber' => 'phone_number',
    ],

    /*
    |--------------------------------------------------------------------------
    | Generators
    |--------------------------------------------------------------------------
    |
    | Here you may specify defaults used when generating Composer classes in
    | your application.
    |
    */

    'generators' => [
        'supports' => ['align', 'mode', 'multiple', 'jsx'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Manifest Path
    |--------------------------------------------------------------------------
    |
    | Here you can define the cache manifest path. Fields are typically cached
    | when running the `acf:cache` command. This will cache the built field
    | groups and potentially improve performance in complex applications.
    |
    */

    'manifest' => storage_path('framework/cache'),

];
