<?php

/**
 * Colors an erroring test's chapter in the E2e recording.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;

final readonly class TestErroredSubscriber implements ErroredSubscriber
{
    public function __construct(private TimelineRecorder $recorder)
    {
    }

    public function notify(Errored $event): void
    {
        $this->recorder->record($event->test(), TimelineEventType::Errored);
    }
}
