<?php

/**
 * Environment variables
 */

declare(strict_types=1);

use function Firehed\Container\env;

return [
    // The unspecified default today is dev=debug, other=warning
    'LOG_LEVEL' => env('LOG_LEVEL', 'warning'),

    // FIXME: this works only for the CLI path, not actual multi-site.
    'OPENEMR_SITE' => env('OPENEMR_SITE', 'default'),
];
