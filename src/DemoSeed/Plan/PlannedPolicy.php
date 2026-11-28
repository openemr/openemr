<?php

/**
 * An insurance_data row to write: one policy of one patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

use OpenEMR\DemoSeed\SeedFile\PolicyType;

final readonly class PlannedPolicy
{
    /**
     * @param ?string $copay dollars with cents, e.g. "25.00"
     * @param ?\DateTimeImmutable $end null for a current policy
     */
    public function __construct(
        public int $pid,
        public PolicyType $type,
        public string $companyKey,
        public string $policyNumber,
        public ?string $groupNumber,
        public PlannedSubscriber $subscriber,
        public ?string $copay,
        public \DateTimeImmutable $start,
        public ?\DateTimeImmutable $end,
    ) {
    }
}
