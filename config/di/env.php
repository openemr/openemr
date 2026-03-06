<?php

use function Firehed\Container\env;

/**
 * Environment variables
 */

return [
    // The unspecified default today is dev=debug, other=warning
    'LOG_LEVEL' => env('LOG_LEVEL', 'warning'),

    'OPENEMR_SITE' => env('OPENEMR_SITE', 'default'),
];
