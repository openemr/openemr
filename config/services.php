<?php

/**
 * Generalized service config.
 */

declare(strict_types=1);

use Firehed\Container\TypedContainerInterface as TC;
use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;

return [
    SystemLogger::class => fn (TC $c) => new SystemLogger($c->get(Level::class)),

    Level::class => fn (TC $c) => Level::fromName($c->get('LOG_LEVEL')),
];

