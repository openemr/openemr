<?php

/**
 * VisitListEntry
 *
 * A patient appointment marked as a Visit List patient (pc_visit_list = 1). It has no time of
 * its own on the list: it stores its block's start time so other screens keep working, and is
 * placed by its list order and, when given, its stop number.
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

final readonly class VisitListEntry
{
    /**
     * @param int               $eventId    pc_eid of the appointment
     * @param int               $patientId  pc_pid of the appointment
     * @param int               $providerId pc_aid of the appointment
     * @param DateTimeImmutable $start      the stored date and start time, used to find its block
     * @param ?int              $sequence   pc_visit_seq: order on the list, null when not yet given
     * @param ?int              $stop       pc_visit_stop: optional stop number, shared by patients at one address
     */
    public function __construct(
        public int $eventId,
        public int $patientId,
        public int $providerId,
        public DateTimeImmutable $start,
        public ?int $sequence,
        public ?int $stop,
    ) {
        if ($eventId <= 0) {
            throw new DomainException('Visit List entry event ID must be positive');
        }
        if ($patientId <= 0) {
            throw new DomainException('Visit List entry patient ID must be positive');
        }
        if ($providerId <= 0) {
            throw new DomainException('Visit List entry provider ID must be positive');
        }
    }
}
