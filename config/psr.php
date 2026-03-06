<?php

/**
 * PSR-specific interface-to-implementation mappings
 */

declare(strict_types=1);

use OpenEMR\Common\Logging\SystemLogger;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => SystemLogger::class,
];
