<?php

declare(strict_types=1);

use OpenEMR\Rector\Rules\CatchExceptionToThrowableRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        CatchExceptionToThrowableRector::class,
    ]);
