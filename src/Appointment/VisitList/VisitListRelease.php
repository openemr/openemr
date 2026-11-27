<?php

/**
 * VisitListRelease
 *
 * Works out the times a Visit List's patients get when their block is deleted (or changed to
 * another category): in list order, one calendar interval apart, starting at the block's
 * start. Times run past the block's old end if need be, but not past the end of the day: the
 * patients that would, share the day's last whole slot, as do all of them when the block
 * starts after that slot begins.
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
use DateTimeZone;
use DomainException;

final class VisitListRelease
{
    /**
     * Each patient's start time, by event ID, in list order: the date and clock time to store
     * (in UTC only so that no clock change applies to them).
     *
     * @return array<int, DateTimeImmutable>
     */
    public static function starts(VisitList $list, int $intervalMinutes): array
    {
        if ($intervalMinutes <= 0) {
            throw new DomainException('Calendar interval must be positive');
        }

        // Clock times, not elapsed time: on a day the clocks change, an hour that repeats or is
        // skipped would otherwise put patients out of list order once stored without an offset.
        $start = new DateTimeImmutable($list->block->start->format('Y-m-d H:i:s'), new DateTimeZone('UTC'));
        $lastSlot = $start->setTime(0, 0)->modify('+1 day')->sub(new DateInterval('PT' . $intervalMinutes . 'M'));
        $starts = [];
        foreach ($list->entries as $index => $entry) {
            $time = $start->add(new DateInterval('PT' . ($index * $intervalMinutes) . 'M'));
            $starts[$entry->eventId] = min($time, $lastSlot);
        }
        return $starts;
    }
}
