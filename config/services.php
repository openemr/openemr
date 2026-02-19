<?php

declare(strict_types=1);

use OpenEMR\Common\Logging\SystemLogger;

return [
    SystemLogger::class => fn() => new SystemLogger('debug'),
    Psr\Log\LoggerInterface::class => SystemLogger::class,
];
