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

use PHPUnit\Event\Application\{
    Finished,
    FinishedSubscriber,
};

/**
 * This is designed as a failsafe in case the SUT calls exit() and causes the
 * tests to abort. If the PHPUnit shutdown event wasn't received, force
 * a nonzero exit code. This should ensure the test suite fails as intended.
 */
class ShutdownTracker implements FinishedSubscriber
{
    private bool $gotFinishedEvent = false;

    public function notify(Finished $event): void
    {
        $this->gotFinishedEvent = true;
    }

    public function install(): void
    {
        register_shutdown_function(function (): void {
            if (!$this->gotFinishedEvent) {
                // @phpstan-ignore openemr.forbiddenErrorLog (System logger not available here)
                error_log("CRITICAL ERROR: Exiting without having received PHPUnit shutdown event");
                exit(70); // "Internal software error"
            }
        });
    }
}
