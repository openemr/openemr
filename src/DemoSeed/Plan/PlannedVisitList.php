<?php

/**
 * A Visit List block to write, with its patients.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

final readonly class PlannedVisitList
{
    /**
     * @param non-empty-list<PlannedVisitListEntry> $entries in Order #
     */
    public function __construct(
        public string $providerKey,
        public string $facilityKey,
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end,
        public array $entries,
    ) {
    }
}
