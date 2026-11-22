<?php

/**
 * VisitListOrder
 *
 * Works out a Visit List's Order # numbers: 1 to n with no gaps or duplicates, in list order.
 * A patient being saved with Order # N goes to place N; whoever held it and everyone after
 * move down one. Without a place, or with one past the end, the patient goes last.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use DomainException;

final class VisitListOrder
{
    /**
     * The Order # of every patient on a list after one of them is put in place.
     *
     * @param list<VisitListEntry> $entries       the list's patients in list order (as VisitListMatcher sorts them)
     * @param ?int                 $movingEventId the patient being put in place, or null to only close gaps
     * @param ?int                 $position      where that patient goes (1 = first), or null for last
     * @return array<int, int> Order # by event ID, for every patient on the list
     */
    public static function place(array $entries, ?int $movingEventId = null, ?int $position = null): array
    {
        if ($position !== null && $position <= 0) {
            throw new DomainException('Visit List position must be positive');
        }

        $moving = null;
        $others = [];
        foreach ($entries as $entry) {
            if ($movingEventId !== null && $entry->eventId === $movingEventId) {
                $moving = $entry;
                continue;
            }
            $others[] = $entry;
        }

        $ordered = $others;
        if ($moving !== null) {
            $index = $position === null ? count($others) : min($position - 1, count($others));
            array_splice($ordered, $index, 0, [$moving]);
        }

        $numbers = [];
        foreach ($ordered as $index => $entry) {
            $numbers[$entry->eventId] = $index + 1;
        }
        return $numbers;
    }
}
