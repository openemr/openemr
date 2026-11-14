<?php

/**
 * VisitListBlock
 *
 * One occurrence of a Visit List provider block: the provider, and the time window on one
 * date that patients on the list belong to. A repeating block yields one of these for each
 * date it falls on, all sharing the series' pc_eid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use DateTimeImmutable;
use DomainException;

final readonly class VisitListBlock
{
    /**
     * @param int               $eventId    pc_eid of the block (shared by every date of a repeating block)
     * @param int               $providerId pc_aid of the block
     * @param int               $facilityId pc_facility of the block (0 when none)
     * @param DateTimeImmutable $start      when the block starts on this date
     * @param DateTimeImmutable $end        when the block ends on this date
     */
    public function __construct(
        public int $eventId,
        public int $providerId,
        public int $facilityId,
        public DateTimeImmutable $start,
        public DateTimeImmutable $end,
    ) {
        if ($eventId <= 0) {
            throw new DomainException('Visit List block event ID must be positive');
        }
        if ($providerId <= 0) {
            throw new DomainException('Visit List block provider ID must be positive');
        }
        if ($end < $start) {
            throw new DomainException('Visit List block cannot end before it starts');
        }
    }

    /**
     * Whether a patient appointment belongs on this block: same provider, starting inside the
     * block's window. The window includes its start and excludes its end, so a block that ends
     * when the next one starts does not claim the next block's patients. A block with no
     * length still claims the patients that start when it does.
     */
    public function contains(VisitListEntry $entry): bool
    {
        if ($entry->providerId !== $this->providerId) {
            return false;
        }

        if ($entry->start == $this->start) {
            return true;
        }

        return $entry->start > $this->start && $entry->start < $this->end;
    }

    /**
     * The date this occurrence falls on (Y-m-d).
     */
    public function date(): string
    {
        return $this->start->format('Y-m-d');
    }
}
