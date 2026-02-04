<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DTO Configuration Path
    |--------------------------------------------------------------------------
    |
    | The path where your DTO configuration files are located.
    | Supports dto.php, dto.xml, dto.yml, or dto.yaml
    |
    */
    'config_path' => config_path(),

    /*
    |--------------------------------------------------------------------------
    | DTO Output Path
    |--------------------------------------------------------------------------
    |
    | The path where generated DTO classes will be written.
    |
    */
    'output_path' => app_path('Dto'),

    /*
    |--------------------------------------------------------------------------
    | DTO Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for generated DTO classes.
    |
    */
    'namespace' => 'App\\Dto',

    /*
    |--------------------------------------------------------------------------
    | TypeScript Output Path
    |--------------------------------------------------------------------------
    |
    | The path where generated TypeScript interfaces will be written.
    |
    */
    'typescript_output_path' => resource_path('js/types'),

    /*
    |--------------------------------------------------------------------------
    | JSON Schema Output Path
    |--------------------------------------------------------------------------
    |
    | The path where generated JSON Schema files will be written.
    |
    */
    'jsonschema_output_path' => resource_path('schemas'),
];
