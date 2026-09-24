<?php

declare(strict_types=1);

use OpenEMR\Rector\Rules\UrlencodeConcatToQueryStringRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        UrlencodeConcatToQueryStringRector::class,
    ]);
