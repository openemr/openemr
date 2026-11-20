<?php

/**
 * How long ago a phone or address stopped being used, and why.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class Inactivation
{
    public function __construct(
        public DayRange $endedDaysAgo,
        public string $reason,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['ended_days_ago', 'reason']);
        return new self(
            DayRange::fromValue($map->raw('ended_days_ago'), $map->pathOf('ended_days_ago')),
            $map->string('reason'),
        );
    }
}
