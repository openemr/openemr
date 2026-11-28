<?php

/**
 * An earlier policy of a case patient that ended; the current policy of the
 * same type starts the next day.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class CaseEndedPolicy
{
    public function __construct(
        public PolicyType $type,
        public string $companyKey,
        public DayRange $endedDaysAgo,
        public ?string $policyNumber,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['type', 'company', 'ended_days_ago', 'policy']);
        return new self(
            PolicyType::parse($map, 'type'),
            $map->string('company'),
            DayRange::fromValue($map->raw('ended_days_ago'), $map->pathOf('ended_days_ago')),
            CasePolicy::number($map, 'policy'),
        );
    }
}
