<?php

/**
 * PSR-specific interface-to-implementation mappings
 */

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use OpenEMR\Common\Logging\SystemLogger;

return [
    LoggerInterface::class => SystemLogger::class,
];
