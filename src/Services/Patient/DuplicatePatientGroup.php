<?php

/**
 * DuplicatePatientGroup is one cluster of charts that look like the same person.
 *
 * A group is anchored by a primary row and lists the charts that scored as duplicates of it. Every
 * patient appears in at most one group: once a chart has been listed, later groups skip it, which
 * is what keeps the report from repeating the same pair from both directions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

final readonly class DuplicatePatientGroup
{
    /**
     * @param int                       $number  1-based position of the group on the report.
     * @param list<DuplicatePatientRow> $matches Never empty; a primary with no matches is not a group.
     */
    public function __construct(
        public int $number,
        public DuplicatePatientRow $primary,
        public array $matches,
    ) {
    }

    /**
     * Every row in report order: the primary first, then its matches.
     *
     * @return list<DuplicatePatientRow>
     */
    public function getRows(): array
    {
        return [$this->primary, ...$this->matches];
    }
}
