<?php

/**
 * How a test ended, as shown in the E2e recording's chapter titles.
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
 * Backed by the label that prefixes each chapter title, so a viewer scrubbing
 * the recording can tell at a glance which chapters are worth watching.
 */
enum TestOutcome: string
{
    case Passed = 'PASS';
    case Failed = 'FAIL';
    case Errored = 'ERROR';
    case Skipped = 'SKIP';

    /**
     * Null for the events that describe when a test ran rather than how it
     * ended.
     */
    public static function fromEventType(TimelineEventType $event): ?self
    {
        return match ($event) {
            TimelineEventType::Failed => self::Failed,
            TimelineEventType::Errored => self::Errored,
            TimelineEventType::Skipped => self::Skipped,
            TimelineEventType::Started, TimelineEventType::Finished => null,
        };
    }
}
