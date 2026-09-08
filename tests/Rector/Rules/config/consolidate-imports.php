<?php

declare(strict_types=1);

use OpenEMR\Rector\Rules\ConsolidateImportsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        ConsolidateImportsRector::class,
    ]);
