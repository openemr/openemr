<?php

declare(strict_types=1);

namespace OpenEMR\PHPUnit;

use PHPUnit\Runner\Extension\Extension as PHPUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class Extension implements PHPUnitExtension
{
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters
    ): void {
        $shutdownTracker = new ShutdownTracker();
        $shutdownTracker->install();
        $facade->registerSubscriber($shutdownTracker);
    }
}
