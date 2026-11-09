<?php

/**
 * A run of consecutive days on which one policy heads its level, within the
 * range asked about (EffectiveInsuranceRule::segments()).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Insurance;

use DateTimeImmutable;

final readonly class CoverageSegment
{
    /**
     * The days are calendar days at midnight in PHP's default time zone, as
     * EffectiveInsurance's start and end are.
     *
     * @param EffectiveInsurance $policy the policy, with its own start and end as EffectiveInsurance gives them
     * @param DateTimeImmutable  $from   the run's first day (the range's first day when it began earlier)
     * @param DateTimeImmutable  $to     the run's last day, included (the range's last day when it goes on)
     */
    public function __construct(
        public EffectiveInsurance $policy,
        public DateTimeImmutable $from,
        public DateTimeImmutable $to,
    ) {
    }
}
