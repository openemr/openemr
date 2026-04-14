<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit;

use PHPUnit\Runner\Extension\Extension as PHPUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * https://docs.phpunit.de/en/12.5/extending-phpunit.html#implementing-an-extension
 */
class Extension implements PHPUnitExtension
{
    /**
     * Tracks if bootstrapping occurred. PHPUnit itself instantiates this class
     * so we can't track the setup ourselves.
     */
    private static bool $isBootstrapped = false;

    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters
    ): void {
        $shutdownTracker = new ShutdownTracker();
        $shutdownTracker->install();
        $facade->registerSubscriber($shutdownTracker);

        self::$isBootstrapped = true;
    }

    public static function isBootstrapped(): bool
    {
        return self::$isBootstrapped;
    }
}
