<?php

/**
 * Marks the point in the E2e recording where a test starts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;

/**
 * PHPUnit maps a subscriber to the first event interface it implements, so
 * each recorded event needs its own subscriber class.
 */
final readonly class TestPreparedSubscriber implements PreparedSubscriber
{
    public function __construct(private TimelineRecorder $recorder)
    {
    }

    public function notify(Prepared $event): void
    {
        $this->recorder->record($event->test(), TimelineEventType::Started);
    }
}
