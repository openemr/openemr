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

use OpenEMR\BC\Deprecation;
use OpenEMR\BC\DeprecationMode;
use OpenEMR\PHPUnit\Timeline\TestErroredSubscriber;
use OpenEMR\PHPUnit\Timeline\TestFailedSubscriber;
use OpenEMR\PHPUnit\Timeline\TestFinishedSubscriber;
use OpenEMR\PHPUnit\Timeline\TestPreparedSubscriber;
use OpenEMR\PHPUnit\Timeline\TestSkippedSubscriber;
use OpenEMR\PHPUnit\Timeline\TimelineRecorder;
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
        Deprecation::$mode = DeprecationMode::Error;

        $shutdownTracker = new ShutdownTracker();
        $shutdownTracker->install();
        $facade->registerSubscriber($shutdownTracker);

        // Records when each E2e test ran so CI can chapter the video
        // recording of the suite. Writes nothing for any other suite.
        $timeline = new TimelineRecorder();
        $facade->registerSubscriber(new TestPreparedSubscriber($timeline));
        $facade->registerSubscriber(new TestFinishedSubscriber($timeline));
        $facade->registerSubscriber(new TestFailedSubscriber($timeline));
        $facade->registerSubscriber(new TestErroredSubscriber($timeline));
        $facade->registerSubscriber(new TestSkippedSubscriber($timeline));

        self::$isBootstrapped = true;
    }

    public static function isBootstrapped(): bool
    {
        return self::$isBootstrapped;
    }
}
