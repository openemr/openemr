<?php

return [
    // Directories or files to scan
    'paths' => [
        __DIR__ . '/_rest_routes.inc.php',
        __DIR__ . '/apis/routes',
    ],

    // Output format: json (default) or yaml
    'output' => [
        'format' => 'yaml',
    ],

    // where to save output when using --config mode
    'destination' => __DIR__ . '/swagger/openemr-api.yaml',

    // optional: enable warnings
    'analysis' => [
        'validate' => true,
    ],
];
