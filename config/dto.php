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
];
