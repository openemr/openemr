<?php

/**
 * The test lifecycle events recorded in the E2e video timeline.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

/**
 * Backed because the value is written to (and read back from) the timeline
 * file.
 */
enum TimelineEventType: string
{
    case Started = 'started';
    case Finished = 'finished';
    case Failed = 'failed';
    case Errored = 'errored';
    case Skipped = 'skipped';
}
