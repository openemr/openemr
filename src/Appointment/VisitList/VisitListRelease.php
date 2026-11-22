<?php

/**
 * VisitListRelease
 *
 * Works out the times a Visit List's patients get when their block is deleted (or changed to
 * another category): in list order, one calendar interval apart, starting at the block's
 * start. Times run past the block's old end if need be, but not past the end of the day: the
 * patients that would, share the day's last slot.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use DateInterval;
use DateTimeImmutable;
use DomainException;

final class VisitListRelease
{
    /**
     * Each patient's start time, by event ID, in list order.
     *
     * @return array<int, DateTimeImmutable>
     */
    public static function starts(VisitList $list, int $intervalMinutes): array
    {
        if ($intervalMinutes <= 0) {
            throw new DomainException('Calendar interval must be positive');
        }

        $start = $list->block->start;
        $lastSlot = $start->setTime(0, 0)->modify('+1 day')->sub(new DateInterval('PT' . $intervalMinutes . 'M'));
        $starts = [];
        foreach ($list->entries as $index => $entry) {
            $time = $start->add(new DateInterval('PT' . ($index * $intervalMinutes) . 'M'));
            $starts[$entry->eventId] = min($time, max($lastSlot, $start));
        }
        return $starts;
    }
}
