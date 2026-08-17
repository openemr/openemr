<?php

/**
 * Colors a failing test's chapter in the E2e recording.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;

final readonly class TestFailedSubscriber implements FailedSubscriber
{
    public function __construct(private TimelineRecorder $recorder)
    {
    }

    public function notify(Failed $event): void
    {
        $this->recorder->record($event->test(), TimelineEventType::Failed);
    }
}
