<?php

/**
 * Marks the point in the E2e recording where a test ends.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final readonly class TestFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(private TimelineRecorder $recorder)
    {
    }

    public function notify(Finished $event): void
    {
        $this->recorder->record($event->test(), TimelineEventType::Finished);
    }
}
